<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\SeniorCitizenBeneficiary;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use App\Mail\BeneficiaryVerified;
use App\Mail\BeneficiaryDisabled;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\SmsService;

class RegisteredSeniorCitizenController extends Controller
{

    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index(Request $request, $encryptedBarangayId)
    {
        try {
            $barangayId = Crypt::decrypt($encryptedBarangayId);
            $barangay = Barangay::findOrFail($barangayId);

            // For verified beneficiaries
            $verifiedCitizens = Beneficiary::where('barangay_id', $barangayId)
                ->where('verified', true)
                ->where(function($q) {
                    $q->where('beneficiary_type', 'Senior Citizen')
                      ->orWhere('beneficiary_type', 'senior citizen');
                })
                ->get();

            // For not verified beneficiaries
            $notVerifiedCitizens = Beneficiary::where('barangay_id', $barangayId)
                ->where('verified', false)
                ->where(function($q) {
                    $q->where('beneficiary_type', 'Senior Citizen')
                      ->orWhere('beneficiary_type', 'senior citizen');
                })
                ->get();

            $notVerifiedCount = $notVerifiedCitizens->count();

            // replace any ->get() that builds $verifiedBeneficiaries with paginate()
            $perPage = 15;
            $verifiedBeneficiaries = Beneficiary::where('verified', true)
                ->where('barangay_id', $barangay->id)
                ->orderBy('last_name')
                ->paginate($perPage)
                ->appends($request->except('page')); // keep query params on pagination links

            return view(
                'content.admin-interface.beneficiaries.senior-citizen.manage-senior-citizens',
                compact('barangay', 'verifiedCitizens', 'notVerifiedCitizens', 'notVerifiedCount', 'encryptedBarangayId', 'verifiedBeneficiaries')
            );
        } catch (\Exception $e) {
            abort(404, 'Invalid Barangay ID');
        }
    }

    public function barangaySelection()
    {
        // Fetch all barangays
        $barangays = Barangay::all();

        return view('content.admin-interface.beneficiaries.senior-citizen.registered-senior-barangay-selection', compact('barangays'));
    }

    public function viewSeniorCitizens($barangayId)
    {
        // Decrypt the barangay ID
        $barangayId = Crypt::decrypt($barangayId);

        // Fetch the barangay and its senior citizen beneficiaries
        $barangay = Barangay::findOrFail($barangayId);
        $seniorCitizens = Beneficiary::where('barangay_id', $barangayId)
            ->where('beneficiary_type', 'Senior Citizen')
            ->get();

        return view('content.admin-interface.beneficiaries.senior-citizen.registered-senior-citizens', compact('barangay', 'seniorCitizens'));
    }


    public function manageSeniorCitizens($encryptedBarangayId)
    {
        try {
            $barangayId = Crypt::decrypt($encryptedBarangayId);
            $barangay = Barangay::findOrFail($barangayId);

            // For verified beneficiaries
            $verifiedCitizens = Beneficiary::where('barangay_id', $barangayId)
                ->where('verified', true)
                ->where(function($q) {
                    $q->where('beneficiary_type', 'Senior Citizen')
                      ->orWhere('beneficiary_type', 'senior citizen');
                })
                ->get();

            // For not verified beneficiaries
            $notVerifiedCitizens = Beneficiary::where('barangay_id', $barangayId)
                ->where('verified', false)
                ->where(function($q) {
                    $q->where('beneficiary_type', 'Senior Citizen')
                      ->orWhere('beneficiary_type', 'senior citizen');
                })
                ->get();

            $notVerifiedCount = $notVerifiedCitizens->count();

            // Pass the encrypted id so views can build links
            return view(
                'content.admin-interface.beneficiaries.senior-citizen.manage-senior-citizens',
                compact('barangay', 'verifiedCitizens', 'notVerifiedCitizens', 'notVerifiedCount', 'encryptedBarangayId')
            );
        } catch (\Exception $e) {
            abort(404, 'Invalid Barangay ID');
        }
    }

    // change verify/disable/edit/delete methods to accept encrypted id and decrypt inside
    public function verifyBeneficiary($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $beneficiary = Beneficiary::findOrFail($id);
        $beneficiary->verified = true;
        $beneficiary->save();

        // Attempt email but do not flash email errors to the UI (only log)
        try {
            Mail::to($beneficiary->email)->send(new BeneficiaryVerified($beneficiary));
        } catch (\Throwable $e) {
            Log::warning('Beneficiary verification email failed', [
                'beneficiary_id' => $beneficiary->id,
                'error' => $e->getMessage(),
            ]);
            // do NOT set session error for email — user requested SMS-only debug display
        }

        // Prepare sms debug info
        $smsDebug = null;
        try {
            $phone = $beneficiary->mobile_number ?? $beneficiary->contact_number ?? $beneficiary->phone ?? $beneficiary->cellphone ?? $beneficiary->contact_no ?? null;

            if ($phone) {
                $digits = preg_replace('/\D+/', '', (string) $phone);

                if (preg_match('/^09\d{9}$/', $digits)) {
                    $recipient = '63' . substr($digits, 1);
                } elseif (preg_match('/^63\d{10}$/', $digits)) { // <-- changed: accept 63 + 10 digits
                    $recipient = $digits;
                } else {
                    \Illuminate\Support\Facades\Log::warning('Invalid phone for beneficiary', [
                        'beneficiary_id' => $beneficiary->id,
                        'phone' => $digits,
                    ]);
                    $smsDebug = 'Invalid phone format: ' . $digits;
                    $recipient = null;
                }

                if ($recipient) {
                    $message = 'Your verification is complete. You are now verified with MSWD.';
                    $sender = config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', 'PhilSMS'));

                    if (empty($sender)) {
                        $smsDebug = 'No PHILSMS sender configured (PHILSMS_DEFAULT_SENDER).';
                        Log::error('No PHILSMS sender configured.');
                    } else {
                        $result = $this->smsService->sendBulkSms([$recipient], $message, $sender, null);

                        // Compact, safe logging: do not store large raw payloads or tokens
                        $status = strtolower((string)($result['status'] ?? 'error'));
                        $apiMessage = $result['message'] ?? null;
                        if (empty($apiMessage) && isset($result['raw']) && is_array($result['raw'])) {
                            $apiMessage = $result['raw']['message'] ?? json_encode(array_slice($result['raw'], 0, 3));
                        }

                        Log::info('PhilSMS verification', [
                            'beneficiary_id' => $beneficiary->id,
                            'recipient'      => $recipient,
                            'sender'         => $sender,
                            'status'         => $status,
                            // truncate message to avoid huge logs
                            'api_message'    => is_string($apiMessage) ? mb_substr($apiMessage, 0, 200) : null,
                        ]);

                        if ($status !== 'success') {
                            $smsDebug = 'PhilSMS error: ' . (is_string($apiMessage) ? $apiMessage : 'Unknown error');
                        } else {
                            $smsDebug = 'PhilSMS success: message accepted';
                        }
                    }
                }
            } else {
                $smsDebug = 'No phone number available for beneficiary.';
            }
        } catch (\Throwable $e) {
            Log::error('SMS send exception for beneficiary verification', [
                'beneficiary_id' => $beneficiary->id,
                'error' => $e->getMessage(),
            ]);
            $smsDebug = 'SMS exception: ' . $e->getMessage();
        }

        // Flash only success and SMS debug (do not flash email errors)
        session()->flash('success', 'Beneficiary verified successfully and notification processed.');
        session()->flash('sms_debug', $smsDebug);

        return back();
    }


    public function selectBarangay()
    {
        $barangays = Barangay::withCount([
            'beneficiaries as verified_count' => function ($query) {
                $query->where('verified', true)
                      ->where(function($q) {
                          $q->where('beneficiary_type', 'Senior Citizen')
                            ->orWhere('beneficiary_type', 'senior citizen');
                      });
            },
            'beneficiaries as not_verified_count' => function ($query) {
                $query->where('verified', false)
                      ->where(function($q) {
                          $q->where('beneficiary_type', 'Senior Citizen')
                            ->orWhere('beneficiary_type', 'senior citizen');
                      });
            }
        ])->get();

        return view('content.admin-interface.beneficiaries.senior-citizen.select-barangay', compact('barangays'));
    }

    public function verifiedBeneficiaries($encryptedBarangayId, Request $request)
    {
        $barangayId = Crypt::decrypt($encryptedBarangayId);
        $barangay = Barangay::findOrFail($barangayId);

        $query = Beneficiary::where('barangay_id', $barangayId)
            ->where('verified', true)
            ->where(function($q) {
                $q->where('beneficiary_type', 'Senior Citizen')
                  ->orWhere('beneficiary_type', 'senior citizen');
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('osca_number', 'like', "%{$search}%");
            });
        }

        $perPage = 15;
        $verifiedBeneficiaries = $query->orderBy('last_name')
            ->paginate($perPage)
            ->appends($request->except('page'));

        return view(
            'content.admin-interface.beneficiaries.senior-citizen.view-registered-senior-citizen.verified-beneficiaries',
            compact('barangay', 'verifiedBeneficiaries', 'encryptedBarangayId')
        );
    }

    public function notVerifiedBeneficiaries($encryptedBarangayId, Request $request)
    {
        $barangayId = Crypt::decrypt($encryptedBarangayId);
        $barangay = Barangay::findOrFail($barangayId);

        $query = Beneficiary::where('barangay_id', $barangayId)
            ->where('verified', false)
            ->where(function($q) {
                $q->where('beneficiary_type', 'Senior Citizen')
                  ->orWhere('beneficiary_type', 'senior citizen');
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('osca_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = 15;
        $notVerifiedBeneficiaries = $query->orderBy('last_name')
            ->paginate($perPage)
            ->appends($request->except('page'));

        return view(
            'content.admin-interface.beneficiaries.senior-citizen.view-registered-senior-citizen.not-verified-beneficiaries',
            compact('barangay', 'notVerifiedBeneficiaries', 'encryptedBarangayId')
        );
    }

    public function disableBeneficiary($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $beneficiary = Beneficiary::findOrFail($id);
        $beneficiary->verified = false;
        $beneficiary->save();

        // Send disabled notification email
        try {
            Mail::to($beneficiary->email)->send(new BeneficiaryDisabled($beneficiary));
        } catch (\Exception $e) {
            return back()->with('error', 'Beneficiary disabled but email could not be sent.');
        }

        return back()->with('success', 'Beneficiary verification disabled and notification sent.');
    }

    public function deleteBeneficiary($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $beneficiary = Beneficiary::findOrFail($id);
        $beneficiary->delete();

        return back()->with('success', 'Beneficiary deleted successfully.');
    }

    public function exportCsv($encryptedBarangayId)
    {
        try {
            $barangayId = \Illuminate\Support\Facades\Crypt::decrypt($encryptedBarangayId);
            $beneficiaries = \App\Models\Beneficiary::where('barangay_id', $barangayId)
                ->where(function($q) {
                    $q->where('beneficiary_type', 'Senior Citizen')
                      ->orWhere('beneficiary_type', 'senior citizen');
                })
                ->get();

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="senior_citizens.csv"',
            ];

            $columns = [
                'last_name', 'first_name', 'middle_name', 'birthday', 'age', 'gender',
                'civil_status', 'osca_number', 'date_issued', 'remarks', 'email'
            ];

            $callback = function () use ($beneficiaries, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($beneficiaries as $beneficiary) {
                    $row = [];
                    foreach ($columns as $col) {
                        $row[] = $beneficiary->$col;
                    }
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to export CSV.');
        }
    }

    public function relatedSearch(Request $request)
    {
        $search = $request->input('search');
        $results = [];
        if ($search) {
            $results = SeniorCitizenBeneficiary::where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('osca_number', 'like', "%{$search}%")
                      ->orWhere('birthday', 'like', "%{$search}%");
                })
                ->limit(10)
                ->get(['id','first_name','last_name','middle_name','age','gender','osca_number','birthday','remarks']);
        }
        return response()->json(['results' => $results]);
    }
}
