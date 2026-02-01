<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Beneficiary;
use App\Models\BeneficiaryDocument;
use App\Models\AidProgramRequirementBeneficiary;
use App\Models\AidProgram;
use App\Models\Barangay;
use App\Models\Schedule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentVerifiedMail;
use App\Mail\DocumentDisabledMail;
use App\Mail\DocumentRejectedMail;
use App\Mail\DocumentEnabledMail;
use App\Services\SmsService;

class DocumentManagementController extends Controller
{
    // Show documents for a specific beneficiary
    public function manageRegisteredDocument($beneficiaryId)
    {
        $beneficiary = Beneficiary::findOrFail($beneficiaryId);
        $documents = BeneficiaryDocument::where('beneficiary_id', $beneficiaryId)->get();

        return view('content.admin-interface.document.manage-registered-document', compact('beneficiary', 'documents'));
    }

    // Download a document
    public function download($id)
    {
        $document = BeneficiaryDocument::findOrFail($id);
        $path = $document->file_path;

        if (!Storage::exists($path)) {
            abort(404, 'File not found in storage: ' . $path);
        }

        return Storage::download($path, basename($path));
    }

    // Show 2 registered beneficiaries (adjust query as needed)
    public function registeredBeneficiariesList($type = null)
    {
        $beneficiaries = Beneficiary::where('verified', true)
            ->where('beneficiary_type', $type)
            ->get();

        return view('content.admin-interface.document.registered-beneficiaries-list', compact('beneficiaries', 'type'));
    }

    // Show documents for a specific beneficiary
    public function registeredBeneficiariesDocuments(string $type)
    {
        $type = strtolower($type); // ensure consistent

        // map common aliases to search keywords
        $aliasMap = [
            'senior' => ['senior', 'senior citizen', 'senior_citizen', 'senior-citizen'],
            'pwd'    => ['pwd', 'person with disability', 'person-with-disability', 'p w d'],
        ];

        $keywords = $aliasMap[$type] ?? [$type];

        $documents = \App\Models\BeneficiaryDocument::with('beneficiary')
            ->whereHas('beneficiary', function ($q) use ($keywords) {
                $q->where('verified', true)
                  ->where(function ($q2) use ($keywords) {
                      foreach ($keywords as $kw) {
                          $q2->orWhere('beneficiary_type', 'like', '%' . $kw . '%');
                      }
                  });
            })
            ->orderByDesc('created_at')
            ->get();

        // optional: help debugging if empty
        if ($documents->isEmpty()) {
            Log::debug('documents-empty-for-type', ['type' => $type]);
        }

        return view('content.admin-interface.document.registered-beneficiaries-documents', compact('documents', 'type'));
    }

    public function selector()
    {
        return view('content.admin-interface.document.beneficiary-type-selector');
    }

    public function programsByType($type)
    {
        $type = strtolower($type);
        $programs = AidProgram::where('beneficiary_type', 'like', "%{$type}%")->get();
        return view('content.admin-interface.document.programs-by-type', compact('programs', 'type'));
    }

    public function registeredBeneficiariesForProgram($programId, $type)
    {
        $type = strtolower($type);
        $program = AidProgram::findOrFail($programId);

        $beneficiaries = Beneficiary::where('verified', true)
            ->whereRaw('LOWER(beneficiary_type) LIKE ?', ["%{$type}%"])
            ->get();

        // Attach documents for this program
        foreach ($beneficiaries as $beneficiary) {
            $beneficiary->documents = BeneficiaryDocument::where('beneficiary_id', $beneficiary->id)
                ->where('aid_type', $program->aid_program_name)
                ->get();
        }

        return view('content.admin-interface.document.registered-beneficiaries-documents', compact('beneficiaries', 'program', 'type'));
    }

    public function verifiedBeneficiariesDocuments(Request $request)
    {
        // Optionally paginate: ->paginate(20)
        $documents = \App\Models\BeneficiaryDocument::with('beneficiary')
            ->whereHas('beneficiary', function ($q) {
                $q->where('verified', true);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('content.admin-interface.document.verified-documents', compact('documents'));
    }

    public function viewPdf($id)
    {
        $document = BeneficiaryDocument::find($id);
        if (! $document) {
            abort(404, 'Document record not found: id='.$id);
        }

        $filePath = $document->file_path;

        // ensure file exists via Storage
        if (! Storage::exists($filePath)) {
            abort(404, 'File not found in storage: ' . $filePath);
        }

        // get the absolute local path from the storage disk
        $absolute = Storage::disk(config('filesystems.default'))->path($filePath);

        if (! file_exists($absolute)) {
            // fallback: try converting slashes (Windows oddities)
            $absoluteAlt = str_replace('/', DIRECTORY_SEPARATOR, $absolute);
            if (! file_exists($absoluteAlt)) {
                abort(404, 'File not found on disk: ' . $absolute);
            }
            $absolute = $absoluteAlt;
        }

        return response()->file($absolute, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.basename($absolute).'"'
        ]);
    }

    public function verify(Request $request, $id)
    {
        $request->validate([
            'expires_at' => 'nullable|date',
            'send_email' => 'nullable|boolean',
            'arb_id'     => 'nullable|integer',
        ]);

        $document = BeneficiaryDocument::findOrFail($id);
        $arbId = $request->input('arb_id');
        $smsService = new SmsService();
        $smsDebug = null;

        if ($arbId) {
            $arb = AidProgramRequirementBeneficiary::findOrFail($arbId);

            if ($arb->beneficiary_document_id != $id) {
                abort(400, 'Document does not match the specified requirement record.');
            }

            $arb->status = 'Validated';
            $arb->validated_at = now();
            $arb->save();

            // Send SMS notification (like RegisteredPWDController)
            $phone = $arb->beneficiary->mobile_number ?? $arb->beneficiary->contact_number ?? $arb->beneficiary->phone ?? $arb->beneficiary->cellphone ?? $arb->beneficiary->contact_no ?? null;
            if ($phone) {
                $digits = preg_replace('/\D+/', '', (string) $phone);
                $recipient = null;
                if (preg_match('/^09\d{9}$/', $digits)) {
                    $recipient = '63' . substr($digits, 1);
                } elseif (preg_match('/^63\d{10}$/', $digits)) {
                    $recipient = $digits;
                }
                if ($recipient) {
                    $message = 'Your document for "' . ($arb->aidProgram->aid_program_name ?? 'Aid Program') . '" has been validated by the admin.';
                    $sender = config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', 'PhilSMS'));
                    if (empty($sender)) {
                        $smsDebug = 'No PHILSMS sender configured (PHILSMS_DEFAULT_SENDER).';
                    } else {
                        $result = $smsService->sendBulkSms([$recipient], $message, $sender);
                        $status = strtolower((string)($result['status'] ?? 'error'));
                        $apiMessage = $result['message'] ?? null;
                        if (empty($apiMessage) && isset($result['raw']) && is_array($result['raw'])) {
                            $apiMessage = $result['raw']['message'] ?? json_encode(array_slice($result['raw'], 0, 3));
                        }
                        if ($status !== 'success') {
                            $smsDebug = 'PhilSMS error: ' . (is_string($apiMessage) ? $apiMessage : 'Unknown error');
                        } else {
                            $smsDebug = 'PhilSMS success: message accepted';
                        }
                    }
                } else {
                    $smsDebug = 'Invalid phone format: ' . $digits;
                }
            } else {
                $smsDebug = 'No phone number available for beneficiary.';
            }

            // Optionally notify beneficiary by email
            if ($request->boolean('send_email') && $arb->beneficiary && $arb->beneficiary->email) {
                Mail::to($arb->beneficiary->email)->send(new DocumentVerifiedMail($document));
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Document validated for the specified program requirement.']);
            }

            return redirect()->back()
                ->with('doc_success_' . $id, 'Document validated for the specified program requirement.')
                ->with('sms_debug', $smsDebug);
        }

        // No arb_id: legacy/global behavior (validate document and update all related requirement records)
        $document->status = 'Validated';
        $document->verified_at = now();
        $document->verified_by = Auth::id();
        $document->expires_at = $request->expires_at ? now()->parse($request->expires_at) : null;
        $document->rejected_reason = null;
        $document->active = true;
        $document->save();

        AidProgramRequirementBeneficiary::where('beneficiary_document_id', $id)
            ->update([
                'status' => 'Validated',
                'validated_at' => now(),
            ]);

        // Send SMS notification (like RegisteredPWDController)
        $phone = $document->beneficiary->mobile_number ?? $document->beneficiary->contact_number ?? $document->beneficiary->phone ?? $document->beneficiary->cellphone ?? $document->beneficiary->contact_no ?? null;
        if ($phone) {
            $digits = preg_replace('/\D+/', '', (string) $phone);
            $recipient = null;
            if (preg_match('/^09\d{9}$/', $digits)) {
                $recipient = '63' . substr($digits, 1);
            } elseif (preg_match('/^63\d{10}$/', $digits)) {
                $recipient = $digits;
            }
            if ($recipient) {
                $message = 'Your document has been validated by the admin.';
                $sender = config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', 'PhilSMS'));
                if (empty($sender)) {
                    $smsDebug = 'No PHILSMS sender configured (PHILSMS_DEFAULT_SENDER).';
                } else {
                    $result = $smsService->sendBulkSms([$recipient], $message, $sender);
                    $status = strtolower((string)($result['status'] ?? 'error'));
                    $apiMessage = $result['message'] ?? null;
                    if (empty($apiMessage) && isset($result['raw']) && is_array($result['raw'])) {
                        $apiMessage = $result['raw']['message'] ?? json_encode(array_slice($result['raw'], 0, 3));
                    }
                    if ($status !== 'success') {
                        $smsDebug = 'PhilSMS error: ' . (is_string($apiMessage) ? $apiMessage : 'Unknown error');
                    } else {
                        $smsDebug = 'PhilSMS success: message accepted';
                    }
                }
            } else {
                $smsDebug = 'Invalid phone format: ' . $digits;
            }
        } else {
            $smsDebug = 'No phone number available for beneficiary.';
        }

        if ($request->boolean('send_email') && $document->beneficiary && $document->beneficiary->email) {
            Mail::to($document->beneficiary->email)->send(new DocumentVerifiedMail($document));
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Document verified successfully.']);
        }

        return redirect()->back()
            ->with('doc_success_' . $id, 'Document verified successfully.')
            ->with('sms_debug', $smsDebug);
    }

    public function disable(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:2000',
            'send_email' => 'nullable|boolean',
            'arb_id' => 'nullable|integer',
        ]);

        $document = BeneficiaryDocument::findOrFail($id);
        $arbId = $request->input('arb_id');
        $smsService = new SmsService();
        $smsDebug = null;

        if ($arbId) {
            $arb = AidProgramRequirementBeneficiary::findOrFail($arbId);

            if ($arb->beneficiary_document_id != $id) {
                abort(400, 'Document does not match the specified requirement record.');
            }

            if (property_exists($arb, 'active')) {
                $arb->active = false;
            }
            $arb->status = 'Disabled';
            if (property_exists($arb, 'disabled_reason')) {
                $arb->disabled_reason = $request->reason;
            }
            $arb->save();

            // Send SMS notification
            $phone = $arb->beneficiary->mobile_number ?? $arb->beneficiary->contact_number ?? $arb->beneficiary->phone ?? $arb->beneficiary->cellphone ?? $arb->beneficiary->contact_no ?? null;
            if ($phone) {
                $digits = preg_replace('/\D+/', '', (string) $phone);
                $recipient = null;
                if (preg_match('/^09\d{9}$/', $digits)) {
                    $recipient = '63' . substr($digits, 1);
                } elseif (preg_match('/^63\d{10}$/', $digits)) {
                    $recipient = $digits;
                }
                if ($recipient) {
                    $message = 'Your document for "' . ($arb->aidProgram->aid_program_name ?? 'Aid Program') . '" has been disabled by the admin.';
                    $sender = config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', 'PhilSMS'));
                    if (empty($sender)) {
                        $smsDebug = 'No PHILSMS sender configured (PHILSMS_DEFAULT_SENDER).';
                    } else {
                        $result = $smsService->sendBulkSms([$recipient], $message, $sender);
                        $status = strtolower((string)($result['status'] ?? 'error'));
                        $apiMessage = $result['message'] ?? null;
                        if (empty($apiMessage) && isset($result['raw']) && is_array($result['raw'])) {
                            $apiMessage = $result['raw']['message'] ?? json_encode(array_slice($result['raw'], 0, 3));
                        }
                        if ($status !== 'success') {
                            $smsDebug = 'PhilSMS error: ' . (is_string($apiMessage) ? $apiMessage : 'Unknown error');
                        } else {
                            $smsDebug = 'PhilSMS success: message accepted';
                        }
                    }
                } else {
                    $smsDebug = 'Invalid phone format: ' . $digits;
                }
            } else {
                $smsDebug = 'No phone number available for beneficiary.';
            }

            if ($request->boolean('send_email') && $arb->beneficiary && $arb->beneficiary->email) {
                Mail::to($arb->beneficiary->email)->send(new DocumentDisabledMail($document, $request->reason));
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Document disabled for the specified program requirement.']);
            }

            return redirect()->back()->with('success', 'Document disabled for the specified program requirement.')
                ->with('disable_sms', 'Beneficiary has been notified via SMS.')
                ->with('sms_debug', $smsDebug);
        }

        // Legacy/global behavior
        $document->active = false;
        $document->status = 'Disabled';
        $document->disabled_reason = $request->reason;
        $document->save();

        // Send SMS notification
        $phone = $document->beneficiary->mobile_number ?? $document->beneficiary->contact_number ?? $document->beneficiary->phone ?? $document->beneficiary->cellphone ?? $document->beneficiary->contact_no ?? null;
        if ($phone) {
            $digits = preg_replace('/\D+/', '', (string) $phone);
            $recipient = null;
            if (preg_match('/^09\d{9}$/', $digits)) {
                $recipient = '63' . substr($digits, 1);
            } elseif (preg_match('/^63\d{10}$/', $digits)) {
                $recipient = $digits;
            }
            if ($recipient) {
                $message = 'Your document has been disabled by the admin.';
                $sender = config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', 'PhilSMS'));
                if (empty($sender)) {
                    $smsDebug = 'No PHILSMS sender configured (PHILSMS_DEFAULT_SENDER).';
                } else {
                    $result = $smsService->sendBulkSms([$recipient], $message, $sender);
                    $status = strtolower((string)($result['status'] ?? 'error'));
                    $apiMessage = $result['message'] ?? null;
                    if (empty($apiMessage) && isset($result['raw']) && is_array($result['raw'])) {
                        $apiMessage = $result['raw']['message'] ?? json_encode(array_slice($result['raw'], 0, 3));
                    }
                    if ($status !== 'success') {
                        $smsDebug = 'PhilSMS error: ' . (is_string($apiMessage) ? $apiMessage : 'Unknown error');
                    } else {
                        $smsDebug = 'PhilSMS success: message accepted';
                    }
                }
            } else {
                $smsDebug = 'Invalid phone format: ' . $digits;
            }
        } else {
            $smsDebug = 'No phone number available for beneficiary.';
        }

        if ($request->boolean('send_email') && $document->beneficiary && $document->beneficiary->email) {
            Mail::to($document->beneficiary->email)->send(new DocumentDisabledMail($document, $request->reason));
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['message' => 'Document disabled successfully.']);
        }

        return redirect()->back()->with('success', 'Document disabled.')
            ->with('disable_sms', 'Beneficiary has been notified via SMS.')
            ->with('sms_debug', $smsDebug);
    }

    public function enable(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:2000',
            'send_email' => 'nullable|boolean',
            'arb_id' => 'nullable|integer',
        ]);

        $document = BeneficiaryDocument::findOrFail($id);
        $arbId = $request->input('arb_id');
        $smsService = new SmsService();
        $smsDebug = null;

        if ($arbId) {
            $arb = AidProgramRequirementBeneficiary::findOrFail($arbId);

            if ($arb->beneficiary_document_id != $id) {
                abort(400, 'Document does not match the specified requirement record.');
            }

            if (property_exists($arb, 'active')) {
                $arb->active = true;
            }
            $arb->status = 'Validated';
            if (property_exists($arb, 'enabled_reason')) {
                $arb->enabled_reason = $request->reason;
            }
            $arb->save();

            // Send SMS notification
            $phone = $arb->beneficiary->mobile_number ?? $arb->beneficiary->contact_number ?? $arb->beneficiary->phone ?? $arb->beneficiary->cellphone ?? $arb->beneficiary->contact_no ?? null;
            if ($phone) {
                $digits = preg_replace('/\D+/', '', (string) $phone);
                $recipient = null;
                if (preg_match('/^09\d{9}$/', $digits)) {
                    $recipient = '63' . substr($digits, 1);
                } elseif (preg_match('/^63\d{10}$/', $digits)) {
                    $recipient = $digits;
                }
                if ($recipient) {
                    $message = 'Your document for "' . ($arb->aidProgram->aid_program_name ?? 'Aid Program') . '" has been enabled by the admin.';
                    $sender = config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', 'PhilSMS'));
                    if (empty($sender)) {
                        $smsDebug = 'No PHILSMS sender configured (PHILSMS_DEFAULT_SENDER).';
                    } else {
                        $result = $smsService->sendBulkSms([$recipient], $message, $sender);
                        $status = strtolower((string)($result['status'] ?? 'error'));
                        $apiMessage = $result['message'] ?? null;
                        if (empty($apiMessage) && isset($result['raw']) && is_array($result['raw'])) {
                            $apiMessage = $result['raw']['message'] ?? json_encode(array_slice($result['raw'], 0, 3));
                        }
                        if ($status !== 'success') {
                            $smsDebug = 'PhilSMS error: ' . (is_string($apiMessage) ? $apiMessage : 'Unknown error');
                        } else {
                            $smsDebug = 'PhilSMS success: message accepted';
                        }
                    }
                } else {
                    $smsDebug = 'Invalid phone format: ' . $digits;
                }
            } else {
                $smsDebug = 'No phone number available for beneficiary.';
            }

            if ($request->boolean('send_email') && $arb->beneficiary && $arb->beneficiary->email) {
                Mail::to($arb->beneficiary->email)->send(new DocumentEnabledMail($document, $request->reason));
            }

            return redirect()->back()->with('success', 'Document enabled for the specified program requirement.')
                ->with('enable_sms', 'Beneficiary has been notified via SMS.')
                ->with('sms_debug', $smsDebug);
        }

        // Legacy/global behavior
        $document->active = true;
        $document->status = 'Validated';
        $document->enabled_reason = $request->reason;
        $document->save();

        // Send SMS notification
        $phone = $document->beneficiary->mobile_number ?? $document->beneficiary->contact_number ?? $document->beneficiary->phone ?? $document->beneficiary->cellphone ?? $document->beneficiary->contact_no ?? null;
        if ($phone) {
            $digits = preg_replace('/\D+/', '', (string) $phone);
            $recipient = null;
            if (preg_match('/^09\d{9}$/', $digits)) {
                $recipient = '63' . substr($digits, 1);
            } elseif (preg_match('/^63\d{10}$/', $digits)) {
                $recipient = $digits;
            }
            if ($recipient) {
                $message = 'Your document has been enabled by the admin.';
                $sender = config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', 'PhilSMS'));
                if (empty($sender)) {
                    $smsDebug = 'No PHILSMS sender configured (PHILSMS_DEFAULT_SENDER).';
                } else {
                    $result = $smsService->sendBulkSms([$recipient], $message, $sender);
                    $status = strtolower((string)($result['status'] ?? 'error'));
                    $apiMessage = $result['message'] ?? null;
                    if (empty($apiMessage) && isset($result['raw']) && is_array($result['raw'])) {
                        $apiMessage = $result['raw']['message'] ?? json_encode(array_slice($result['raw'], 0, 3));
                    }
                    if ($status !== 'success') {
                        $smsDebug = 'PhilSMS error: ' . (is_string($apiMessage) ? $apiMessage : 'Unknown error');
                    } else {
                        $smsDebug = 'PhilSMS success: message accepted';
                    }
                }
            } else {
                $smsDebug = 'Invalid phone format: ' . $digits;
            }
        } else {
            $smsDebug = 'No phone number available for beneficiary.';
        }

        if ($request->boolean('send_email') && $document->beneficiary && $document->beneficiary->email) {
            Mail::to($document->beneficiary->email)->send(new DocumentEnabledMail($document, $request->reason));
        }

        return redirect()->back()->with('success', 'Document enabled.')
            ->with('enable_sms', 'Beneficiary has been notified via SMS.')
            ->with('sms_debug', $smsDebug);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:2000',
            'send_email' => 'nullable|boolean',
            'arb_id'     => 'nullable|integer',
        ]);

        $document = BeneficiaryDocument::findOrFail($id);
        $arbId = $request->input('arb_id');
        $smsService = new SmsService();
        $smsDebug = null;

        if ($arbId) {
            $arb = AidProgramRequirementBeneficiary::findOrFail($arbId);

            if ($arb->beneficiary_document_id != $id) {
                abort(400, 'Document does not match the specified requirement record.');
            }

            $arb->status = 'Rejected';
            $arb->validated_at = now();
            if (property_exists($arb, 'rejected_reason')) {
                $arb->rejected_reason = $request->reason;
            }
            $arb->save();

            // Send SMS notification
            $phone = $arb->beneficiary->mobile_number ?? $arb->beneficiary->contact_number ?? $arb->beneficiary->phone ?? $arb->beneficiary->cellphone ?? $arb->beneficiary->contact_no ?? null;
            if ($phone) {
                $digits = preg_replace('/\D+/', '', (string) $phone);
                $recipient = null;
                if (preg_match('/^09\d{9}$/', $digits)) {
                    $recipient = '63' . substr($digits, 1);
                } elseif (preg_match('/^63\d{10}$/', $digits)) {
                    $recipient = $digits;
                }
                if ($recipient) {
                    $message = 'Your document for "' . ($arb->aidProgram->aid_program_name ?? 'Aid Program') . '" has been rejected by the admin. Reason: ' . $request->reason;
                    $sender = config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', 'PhilSMS'));
                    if (empty($sender)) {
                        $smsDebug = 'No PHILSMS sender configured (PHILSMS_DEFAULT_SENDER).';
                    } else {
                        $result = $smsService->sendBulkSms([$recipient], $message, $sender);
                        $status = strtolower((string)($result['status'] ?? 'error'));
                        $apiMessage = $result['message'] ?? null;
                        if (empty($apiMessage) && isset($result['raw']) && is_array($result['raw'])) {
                            $apiMessage = $result['raw']['message'] ?? json_encode(array_slice($result['raw'], 0, 3));
                        }
                        if ($status !== 'success') {
                            $smsDebug = 'PhilSMS error: ' . (is_string($apiMessage) ? $apiMessage : 'Unknown error');
                        } else {
                            $smsDebug = 'PhilSMS success: message accepted';
                        }
                    }
                } else {
                    $smsDebug = 'Invalid phone format: ' . $digits;
                }
            } else {
                $smsDebug = 'No phone number available for beneficiary.';
            }

            if ($request->boolean('send_email') && $arb->beneficiary && $arb->beneficiary->email) {
                Mail::to($arb->beneficiary->email)->send(new DocumentRejectedMail($document, $request->reason));
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Document rejected for the specified program requirement.']);
            }

            return redirect()->back()->with('success', 'Document rejected for the specified program requirement.')
                ->with('reject_sms', 'Beneficiary has been notified via SMS.')
                ->with('sms_debug', $smsDebug);
        }

        // Legacy/global behavior: update document and mark inactive
        $document->status = 'Rejected';
        $document->rejected_reason = $request->reason;
        $document->verified_at = now();
        $document->verified_by = Auth::id();
        $document->active = false;
        $document->save();

        // Send SMS notification
        $phone = $document->beneficiary->mobile_number ?? $document->beneficiary->contact_number ?? $document->beneficiary->phone ?? $document->beneficiary->cellphone ?? $document->beneficiary->contact_no ?? null;
        if ($phone) {
            $digits = preg_replace('/\D+/', '', (string) $phone);
            $recipient = null;
            if (preg_match('/^09\d{9}$/', $digits)) {
                $recipient = '63' . substr($digits, 1);
            } elseif (preg_match('/^63\d{10}$/', $digits)) {
                $recipient = $digits;
            }
            if ($recipient) {
                $message = 'Your document has been rejected by the admin. Reason: ' . $request->reason;
                $sender = config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', 'PhilSMS'));
                if (empty($sender)) {
                    $smsDebug = 'No PHILSMS sender configured (PHILSMS_DEFAULT_SENDER).';
                } else {
                    $result = $smsService->sendBulkSms([$recipient], $message, $sender);
                    $status = strtolower((string)($result['status'] ?? 'error'));
                    $apiMessage = $result['message'] ?? null;
                    if (empty($apiMessage) && isset($result['raw']) && is_array($result['raw'])) {
                        $apiMessage = $result['raw']['message'] ?? json_encode(array_slice($result['raw'], 0, 3));
                    }
                    if ($status !== 'success') {
                        $smsDebug = 'PhilSMS error: ' . (is_string($apiMessage) ? $apiMessage : 'Unknown error');
                    } else {
                        $smsDebug = 'PhilSMS success: message accepted';
                    }
                }
            } else {
                $smsDebug = 'Invalid phone format: ' . $digits;
            }
        } else {
            $smsDebug = 'No phone number available for beneficiary.';
        }

        if ($request->boolean('send_email') && $document->beneficiary && $document->beneficiary->email) {
            Mail::to($document->beneficiary->email)->send(new DocumentRejectedMail($document, $request->reason));
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['message' => 'Document rejected successfully.']);
        }

        return redirect()->back()->with('success', 'Document rejected.')
            ->with('reject_sms', 'Beneficiary has been notified via SMS.')
            ->with('sms_debug', $smsDebug);
    }

    public function reverify(Request $request, $id)
    {
        $request->validate([
            'arb_id' => 'nullable|integer', // optional: reverify only a specific requirement-beneficiary record
        ]);

        $document = BeneficiaryDocument::findOrFail($id);
        $arbId = $request->input('arb_id');

        if ($arbId) {
            $arb = AidProgramRequirementBeneficiary::findOrFail($arbId);

            if ($arb->beneficiary_document_id != $id) {
                abort(400, 'Document does not match the specified requirement record.');
            }

            $arb->status = 'Pending Review';
            $arb->validated_at = null;
            $arb->save();

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Document set for re-verification for the specified program requirement.']);
            }

            return redirect()->back()->with('success', 'Document set for re-verification for the specified program requirement.');
        }

        // Legacy/global behavior
        $document->status = 'Pending Review';
        $document->rejected_reason = null;
        $document->verified_at = null;
        $document->verified_by = null;
        $document->active = true;
        $document->save();

        AidProgramRequirementBeneficiary::where('beneficiary_document_id', $id)
            ->update([
                'status' => 'Pending Review',
                'validated_at' => null,
            ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['message' => 'Document set for re-verification.']);
        }

        return redirect()->back()->with('success', 'Document set for re-verification.');
    }


    public function beneficiaryProgramDocuments(Request $request)
    {
        $barangayId = $request->query('barangay_id');
        $beneficiaryId = $request->query('beneficiary_id');

        // Decrypt barangay_id if needed
        if ($barangayId && !is_numeric($barangayId)) {
            try {
                $decrypted = decrypt($barangayId);
                if (is_numeric($decrypted)) {
                    $barangayId = $decrypted;
                }
            } catch (\Throwable $e) {
                // leave as-is if decryption fails
            }
        }

        // Decrypt beneficiary_id if needed
        if ($beneficiaryId && !is_numeric($beneficiaryId)) {
            try {
                $decrypted = decrypt($beneficiaryId);
                if (is_numeric($decrypted)) {
                    $beneficiaryId = $decrypted;
                }
            } catch (\Throwable $e) {
                // leave as-is if decryption fails
            }
        }

        // Validate required params
        if (!$barangayId || !$beneficiaryId) {
            return redirect()->back()->with('error', 'Barangay and Beneficiary are required.');
        }

        $barangay = \App\Models\Barangay::findOrFail($barangayId);
        $beneficiary = \App\Models\Beneficiary::findOrFail($beneficiaryId);

        // Get all documents for this beneficiary
        $documents = \App\Models\BeneficiaryDocument::where('beneficiary_id', $beneficiaryId)->get();

        Log::debug('beneficiaryProgramDocuments-simple', [
            'barangay_id' => $barangayId,
            'beneficiary_id' => $beneficiaryId,
            'documents_count' => $documents->count(),
        ]);

        return view('content.admin-interface.document.beneficiary-program-documents', compact(
            'barangay',
            'beneficiary',
            'documents'
        ));
    }

    public function documentBarangaySelector()
    {
        $barangays = Barangay::all()->map(function ($barangay) {
            return [
                'id' => $barangay->id,
                'barangay_name' => $barangay->barangay_name,
                'encrypted_id' => encrypt($barangay->id),
            ];
        });

        // For blade and AJAX search
        return view('content.admin-interface.document.document-barangay-selector', [
            'barangays' => $barangays,
        ]);
    }

    public function barangayPrograms(Request $request)
    {
        $barangayId = $request->input('barangay_id');
        $programs = \App\Models\AidProgram::where('barangay_id', $barangayId)->get();

        // Return JSON for AJAX
        return response()->json($programs->map(function($program) use ($barangayId) {
            return [
                'aid_program_name' => $program->aid_program_name,
                'description' => $program->description,
                'link' => route('beneficiaries.interface', ['encryptedBarangayId' => encrypt($barangayId), 'program_id' => $program->id])
            ];
        }));
    }

    public function programTypeSelector(Request $request)
    {
        $barangayId = $request->query('barangay_id');
        $beneficiaryType = $request->query('beneficiary_type');
        $beneficiaryId = $request->query('beneficiary_id');

        // attempt to decrypt if encrypted
        if ($barangayId && !is_numeric($barangayId)) {
            try {
                $decrypted = decrypt($barangayId);
                if (is_numeric($decrypted)) {
                    $barangayId = $decrypted;
                }
            } catch (\Throwable $e) {
                // ignore and proceed (will fail findOrFail below if invalid)
            }
        }

        $barangay = Barangay::findOrFail($barangayId);

        // Get all beneficiaries of this type in the barangay (case-insensitive)
        $beneficiaries = Beneficiary::where('barangay_id', $barangayId)
            ->when($beneficiaryType, function($q) use ($beneficiaryType) {
                $q->whereRaw('LOWER(TRIM(beneficiary_type)) LIKE ?', ["%".strtolower(trim($beneficiaryType))."%"]);
            })
            ->get();

        // Optional: single beneficiary
        $beneficiary = null;
        if ($beneficiaryId) {
            $beneficiary = Beneficiary::find($beneficiaryId);
        }

        // Robust schedule -> aid_program lookup: support JSON arrays, CSV, plain value and common variations
        $scheduleQuery = Schedule::where(function($q) use ($barangayId) {
            $q->whereJsonContains('barangay_ids', $barangayId)
              ->orWhereRaw('FIND_IN_SET(?, barangay_ids)', [$barangayId])
              ->orWhere('barangay_ids', $barangayId)
              ->orWhere('barangay_ids', 'like', '%"'.$barangayId.'"%')
              ->orWhere('barangay_ids', 'like', '%,'.$barangayId.',%')
              ->orWhere('barangay_ids', 'like', $barangayId.',%')
              ->orWhere('barangay_ids', 'like', '%,'.$barangayId);
        });

        if ($beneficiaryType) {
            $scheduleQuery->whereRaw('LOWER(TRIM(beneficiary_type)) LIKE ?', ["%".strtolower(trim($beneficiaryType))."%"]);
        }

        $schedulePrograms = $scheduleQuery->pluck('aid_program_id')->unique()->filter()->values()->toArray();

        // If no schedules found, fallback to programs which declare the beneficiary type
        if (empty($schedulePrograms)) {
            $programs = AidProgram::all();
        } else {
            $programs = AidProgram::whereIn('id', $schedulePrograms)->get();
        }

        return view('content.admin-interface.document.view-beneficiary', compact(
            'barangay',
            'beneficiaryType',
            'programs',
            'beneficiary',
            'beneficiaries'
        ));
    }

    public function showRegisteredDocument($beneficiaryId)
    {
        $beneficiary = Beneficiary::findOrFail($beneficiaryId);
        // Fetch files/documents as needed
        return view('content.admin-interface.document.registered-files', compact('beneficiary'));
    }
}
