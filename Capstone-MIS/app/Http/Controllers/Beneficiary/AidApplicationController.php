<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Schedule;
use App\Models\Beneficiary;
use App\Models\BeneficiaryDocument;
use App\Models\Requirement;
use App\Models\AidProgram;
use App\Models\AidProgramRequirementBeneficiary;
use App\Models\AidReceipt;
use Carbon\Carbon;

class AidApplicationController extends Controller
{
    public function index(Request $request)
    {
        $beneficiary = Auth::guard('beneficiary')->user();
        if (!$beneficiary) {
            Log::warning('Unauthorized access to aid application index');
            abort(403, 'Unauthorized');
        }

        $barangayId = (int) ($beneficiary->barangay_id ?? 0);
        if ($barangayId <= 0) {
            Log::warning('Beneficiary missing barangay_id', ['beneficiary_id' => $beneficiary->id]);
            abort(500, 'Configuration error');
        }

        $isSenior = !empty($beneficiary->osca_number);
        $isPwd = !empty($beneficiary->pwd_id);

        if (!$isSenior && !$isPwd) {
            Log::warning('Beneficiary not qualified for aid', [
                'beneficiary_id' => $beneficiary->id,
                'barangay_id' => $barangayId
            ]);
            return view('content.beneficiary-interface.aid-application.aid-application', [
                'activeApplications' => [],
                'historyApplications' => [],
                'message' => 'You are not yet qualified to apply for aid programs. Please complete your registration as a Senior Citizen or PWD.'
            ]);
        }

        $now = now();

        $schedules = Schedule::with(['aidProgram' => function($q) {
                $q->with('requirements');
            }])
            ->where('published', true)
            ->where(function ($q) use ($barangayId) {
                $q->whereJsonContains('barangay_ids', (string) $barangayId)
                  ->orWhereJsonContains('barangay_ids', $barangayId);
            })
            ->where(function ($q) use ($isSenior, $isPwd) {
                $q->whereNull('beneficiary_type')
                  ->orWhere('beneficiary_type', '')
                  ->orWhere('beneficiary_type', 'both')
                  ->orWhere('beneficiary_type', 'Both');

                if ($isSenior) {
                    $q->orWhere('beneficiary_type', 'senior')
                      ->orWhere('beneficiary_type', 'Senior')
                      ->orWhere('beneficiary_type', 'Senior Citizen')
                      ->orWhere('beneficiary_type', 'senior citizen')
                      ->orWhereRaw('LOWER(beneficiary_type) = ?', ['senior']);
                }

                if ($isPwd) {
                    $q->orWhere('beneficiary_type', 'pwd')
                      ->orWhere('beneficiary_type', 'PWD')
                      ->orWhere('beneficiary_type', 'Person With Disability')
                      ->orWhere('beneficiary_type', 'person with disability')
                      ->orWhereRaw('LOWER(beneficiary_type) = ?', ['pwd']);
                }
            })
            ->orderBy('start_date', 'desc')
            ->get();

        Log::info('Aid applications loaded', [
            'beneficiary_id' => $beneficiary->id,
            'barangay_id' => $barangayId,
            'is_senior' => $isSenior,
            'is_pwd' => $isPwd,
            'schedules_count' => $schedules->count()
        ]);

        $activeApplications = [];
        $historyApplications = [];

        foreach ($schedules as $schedule) {
            if ($now->lt($schedule->start_date)) {
                $status = 'Upcoming';
            } elseif ($now->between($schedule->start_date, $schedule->end_date)) {
                $status = 'Ongoing';
            } else {
                $status = 'Completed';
            }

            $requirements = [];
            $requirementIds = [];
            $requirementsStatus = []; // keyed by requirement id => ['label'=>..., 'progress'=>0|50|100]

            if ($schedule->aidProgram && $schedule->aidProgram->requirements && $schedule->aidProgram->requirements->count()) {
                // human readable list
                $requirements = $schedule->aidProgram->requirements
                    ->pluck('document_requirement')
                    ->map(fn($req) => htmlspecialchars($req, ENT_QUOTES, 'UTF-8'))
                    ->toArray();

                $requirementIds = $schedule->aidProgram->requirements->pluck('id')->toArray();

                // compute each requirement's status and per-requirement progress:
                // 0 = Not Applied (no submission)
                // 50 = Applied / uploaded (citizen action)
                // 100 = Received / Validated (admin verified - considered completed)
                foreach ($schedule->aidProgram->requirements as $req) {
                    $arb = AidProgramRequirementBeneficiary::where('beneficiary_id', $beneficiary->id)
                        ->where('aid_program_id', $schedule->aidProgram->id)
                        ->where('requirement_id', $req->id)
                        ->first();

                    $label = 'Not Applied';
                    $prog = 0;

                    if ($arb) {
                        $arbStatus = strtolower(trim((string)$arb->status));
                        // treat 'received' or 'validated' or 'completed' as fully complete
                        if (in_array($arbStatus, ['received', 'validated', 'completed'])) {
                            $label = 'Received';
                            $prog = 100;
                        } elseif (in_array($arbStatus, ['applied', 'pending', 'uploaded'])) {
                            $label = 'Uploaded';
                            $prog = 50;
                        } else {
                            // fallback to 'Pending' if status present but not matched
                            $label = ucfirst($arbStatus ?: 'Pending');
                            $prog = ($arb->beneficiary_document_id ? 50 : 0);
                        }
                    }

                    $requirementsStatus[$req->id] = [
                        'label' => $label,
                        'progress' => $prog,
                    ];
                }
            }

            // compute overall progress for this application
            $progressPercent = 0;
            if (!empty($requirementsStatus)) {
                $sum = array_sum(array_map(fn($r) => $r['progress'], $requirementsStatus));
                $progressPercent = (int) round($sum / count($requirementsStatus));
            } else {
                // If no explicit requirements, mark 100% only if status is Ongoing/Completed
                $progressPercent = $status === 'Completed' ? 100 : 0;
            }

             $app = [
                'id' => 'APP-' . (int) $schedule->id,
                'program_id' => (int) $schedule->aidProgram->id,
                'type' => htmlspecialchars($schedule->aidProgram->aid_program_name ?? 'Unknown', ENT_QUOTES, 'UTF-8'),
                'description' => htmlspecialchars($schedule->aidProgram->description ?? '', ENT_QUOTES, 'UTF-8'),
                'amount' => (int) ($schedule->aidProgram->amount ?? 0),
                'status' => $status,
                'status_type' => $status === 'Ongoing' ? 'primary' : ($status === 'Upcoming' ? 'info' : 'secondary'),
                'progress' => $progressPercent,
                'applied' => Carbon::parse($schedule->start_date)->format('m/d/Y'),
                'updated' => Carbon::parse($schedule->updated_at)->format('m/d/Y'),
                // Pass both start and end dates for the range
                'distribution_start' => Carbon::parse($schedule->start_date)->format('l, F d, Y'),
                'distribution_end'   => Carbon::parse($schedule->end_date)->format('l, F d, Y'),
                'distribution_date'  => Carbon::parse($schedule->start_date)->format('l, F d, Y') . ' to ' . Carbon::parse($schedule->end_date)->format('l, F d, Y'),
                'can_apply' => $status === 'Ongoing',
                'requirements' => $requirements,
                'requirement_ids' => $requirementIds,
                'requirements_status' => $requirementsStatus,
                'beneficiary_type' => htmlspecialchars($schedule->beneficiary_type ?? 'All', ENT_QUOTES, 'UTF-8'),
            ];

            if ($status === 'Completed') {
                $historyApplications[] = $app;
            } else {
                $activeApplications[] = $app;
            }
        }

        Log::info('Aid applications processed', [
            'beneficiary_id' => $beneficiary->id,
            'active_count' => count($activeApplications),
            'history_count' => count($historyApplications)
        ]);

        return view('content.beneficiary-interface.aid-application.aid-application', [
            'activeApplications' => $activeApplications,
            'historyApplications' => $historyApplications,
            'beneficiary' => $beneficiary,
            'beneficiary_type' => $isSenior ? 'Senior Citizen' : ($isPwd ? 'PWD' : 'Unknown'),
        ]);
    }

    public function showSubmitDocumentForm($aidProgramId, $requirementId)
    {
        $beneficiary = Auth::guard('beneficiary')->user();

        $validatedDocs = BeneficiaryDocument::where('beneficiary_id', $beneficiary->id)
            ->where('status', 'Validated')
            ->get();

        $aidProgram = AidProgram::with('requirements')->findOrFail($aidProgramId);

        $selectedRequirementId = request()->get('requirement_id', $requirementId);

        $requirement = $aidProgram->requirements->where('id', $selectedRequirementId)->first();

        return view('content.beneficiary-interface.aid-application.submit-document', [
            'aidProgramId' => $aidProgramId,
            'requirement' => $requirement,
            'requirements' => $aidProgram->requirements,
            'validatedDocs' => $validatedDocs,
        ]);
    }

    public function storeSubmittedDocument(Request $request, $aidProgramId, $requirementId)
    {
        $beneficiary = Auth::guard('beneficiary')->user();

        $request->validate([
            'validated_document_id' => 'nullable|exists:beneficiary_documents,id,beneficiary_id,' . $beneficiary->id,
            'document_file' => 'nullable|file|max:5120|mimes:jpeg,jpg,png,pdf,doc,docx',
        ]);

        if (!$request->validated_document_id && !$request->hasFile('document_file')) {
            return back()->withErrors(['document_file' => 'Please select a validated document or upload a new file.']);
        }

        $newDoc = new BeneficiaryDocument();
        $newDoc->beneficiary_id = $beneficiary->id;
        $newDoc->document_type = Requirement::findOrFail($requirementId)->document_requirement;
        $newDoc->status = 'Pending Review';

        if ($request->validated_document_id) {
            $validatedDoc = BeneficiaryDocument::findOrFail($request->validated_document_id);
            $newDoc->file_path = $validatedDoc->file_path;
        } else {
            $file = $request->file('document_file');
            $path = $file->store('beneficiary_documents', 'public');
            $newDoc->file_path = $path;
        }

        $newDoc->save();

        AidProgramRequirementBeneficiary::updateOrCreate(
            [
                'beneficiary_id' => $beneficiary->id,
                'barangay_id' => $beneficiary->barangay_id,
                'aid_program_id' => $aidProgramId,
                'requirement_id' => $requirementId,
            ],
            [
                'beneficiary_document_id' => $newDoc->id,
                'status' => 'Pending',
                'remarks' => null,
                'validated_at' => null,
            ]
        );

        // Use the provided return_url (or stay on current page) — do NOT redirect to the applications index.
        $returnUrl = $request->input('return_url', url()->current());

        return redirect()->to($returnUrl)->with('success', 'Document submitted successfully. Continue uploading required documents.');
    }

    public function retractDocument($aidProgramId, $requirementId)
    {
        $beneficiary = Auth::guard('beneficiary')->user();

        $record = AidProgramRequirementBeneficiary::where('beneficiary_id', $beneficiary->id)
            ->where('aid_program_id', $aidProgramId)
            ->where('requirement_id', $requirementId)
            ->first();

        if ($record) {
            $record->delete();
        }

        return redirect()->route('beneficiaries.applications')
            ->with('success', 'Document retracted. You may now upload again.');
    }

    /**
     * Redirect beneficiary to the submit-document form for a program.
     * If the program has requirements, redirect to the first requirement's submit page.
     */
    public function apply($aidProgramId)
    {
        $beneficiary = Auth::guard('beneficiary')->user();
        if (! $beneficiary) {
            abort(403, 'Unauthorized');
        }

        $aidProgram = AidProgram::with('requirements')->find($aidProgramId);
        if (! $aidProgram) {
            return redirect()->route('beneficiaries.applications')
                ->with('error', 'Requested aid program not found.');
        }

        $firstRequirement = $aidProgram->requirements->first();
        if (! $firstRequirement) {
            return redirect()->route('beneficiaries.applications')
                ->with('error', 'This program has no requirements to submit.');
        }

        return redirect()->route('beneficiary.submit-document.form', [$aidProgramId, $firstRequirement->id]);
    }

    public function confirmReceived(Request $request, $aidProgramId)
    {
        $beneficiary = Auth::guard('beneficiary')->user();
        if (! $beneficiary) {
            abort(403, 'Unauthorized');
        }

        $aidProgram = AidProgram::find($aidProgramId);
        if (! $aidProgram) {
            return redirect()->back()->with('error', 'Aid program not found.');
        }

        AidReceipt::updateOrCreate(
            [
                'beneficiary_id' => $beneficiary->id,
                'aid_program_id' => $aidProgramId,
            ],
            [
                'schedule_id'  => $request->input('schedule_id') ?: null,
                'receipt_date' => now(),
                'notes'        => $request->input('notes') ?: 'Confirmed by beneficiary',
                'confirmed_by' => null,
            ]
        );

        return redirect()->route('beneficiaries.applications')
            ->with('success', 'Thank you. Receipt confirmed for ' . ($aidProgram->aid_program_name ?? 'the program') . '.');
    }
}
