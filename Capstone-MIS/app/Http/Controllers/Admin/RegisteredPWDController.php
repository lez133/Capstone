<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Beneficiary;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\BeneficiaryVerified;
use App\Mail\BeneficiaryDisabled;
use App\Services\SmsService;

class RegisteredPWDController extends Controller
{
    public function index()
    {
        $notVerifiedCount = Beneficiary::where(function($q) {
                $q->where('beneficiary_type', 'PWD')
                  ->orWhere('beneficiary_type', 'pwd');
            })
            ->where('verified', false)
            ->count();

        return view('content.admin-interface.beneficiaries.pwd.manage-pwds', compact('notVerifiedCount'));
    }

    public function selectBarangay()
    {
        $barangays = Barangay::withCount([
            'beneficiaries as verified_count' => function ($query) {
                $query->where('verified', true)
                      ->where(function($q) {
                          $q->where('beneficiary_type', 'PWD')
                            ->orWhere('beneficiary_type', 'pwd');
                      });
            },
            'beneficiaries as not_verified_count' => function ($query) {
                $query->where('verified', false)
                      ->where(function($q) {
                          $q->where('beneficiary_type', 'PWD')
                            ->orWhere('beneficiary_type', 'pwd');
                      });
            }
        ])->get();

        return view('content.admin-interface.beneficiaries.pwd.select-barangay', compact('barangays'));
    }

    public function managePWDs($encryptedBarangayId)
    {
        try {
            $barangayId = Crypt::decrypt($encryptedBarangayId);
            $barangay = Barangay::findOrFail($barangayId);

            $verifiedPWDs = Beneficiary::where('barangay_id', $barangayId)
                ->where('verified', true)
                ->where(function($q) {
                    $q->where('beneficiary_type', 'PWD')
                      ->orWhere('beneficiary_type', 'pwd');
                })
                ->get();

            $notVerifiedPWDs = Beneficiary::where('barangay_id', $barangayId)
                ->where('verified', false)
                ->where(function($q) {
                    $q->where('beneficiary_type', 'PWD')
                      ->orWhere('beneficiary_type', 'pwd');
                })
                ->get();

            $notVerifiedCount = $notVerifiedPWDs->count();

            return view(
                'content.admin-interface.beneficiaries.pwd.manage-pwds',
                compact('barangay', 'verifiedPWDs', 'notVerifiedPWDs', 'notVerifiedCount', 'encryptedBarangayId')
            );
        } catch (\Exception $e) {
            abort(404, 'Invalid Barangay ID');
        }
    }

    public function verifiedBeneficiaries($encryptedBarangayId, Request $request)
    {
        $barangayId = Crypt::decrypt($encryptedBarangayId);
        $barangay = Barangay::findOrFail($barangayId);

        $query = Beneficiary::where('barangay_id', $barangayId)
            ->where('verified', true)
            ->where(function($q) {
                $q->where('beneficiary_type', 'PWD')
                  ->orWhere('beneficiary_type', 'pwd');
            });

        if ($request->filled('search')) {
            $s = $request->search;
            $columns = ['first_name', 'last_name', 'email', 'pwd_id']; // use existing pwd_id column
            $query->where(function($q) use ($columns, $s) {
                $first = array_shift($columns);
                $q->where($first, 'like', "%{$s}%");
                foreach ($columns as $col) {
                    $q->orWhere($col, 'like', "%{$s}%");
                }
            });
        }

        $perPage = 15;
        $verifiedBeneficiaries = $query->orderBy('last_name')
            ->paginate($perPage)
            ->appends($request->except('page'));

        return view('content.admin-interface.beneficiaries.pwd.view-registered-pwd.verified-beneficiaries', compact('barangay','verifiedBeneficiaries','encryptedBarangayId'));
    }

    public function notVerifiedBeneficiaries($encryptedBarangayId, Request $request)
    {
        $barangayId = Crypt::decrypt($encryptedBarangayId);
        $barangay = Barangay::findOrFail($barangayId);

        $query = Beneficiary::where('barangay_id', $barangayId)
            ->where('verified', false)
            ->where(function($q) {
                $q->where('beneficiary_type', 'PWD')
                  ->orWhere('beneficiary_type', 'pwd');
            });

        if ($request->filled('search')) {
            $s = $request->search;
            $columns = ['first_name', 'last_name', 'email', 'pwd_id']; // use existing pwd_id column
            $query->where(function($q) use ($columns, $s) {
                $first = array_shift($columns);
                $q->where($first, 'like', "%{$s}%");
                foreach ($columns as $col) {
                    $q->orWhere($col, 'like', "%{$s}%");
                }
            });
        }

        $perPage = 15;
        $notVerifiedBeneficiaries = $query->orderBy('last_name')
            ->paginate($perPage)
            ->appends($request->except('page'));

        return view('content.admin-interface.beneficiaries.pwd.view-registered-pwd.not-verified-beneficiaries', compact('barangay','notVerifiedBeneficiaries','encryptedBarangayId'));
    }

    public function verifyBeneficiary($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);
        $beneficiary->verified = true;
        $beneficiary->save();

        // Send verification email
        try {
            Mail::to($beneficiary->email)->send(new BeneficiaryVerified($beneficiary));
        } catch (\Exception $e) {
            Log::warning('PWD verification email failed', [
                'beneficiary_id' => $beneficiary->id,
                'error' => $e->getMessage(),
            ]);
            // do not stop flow on email failure
        }

        // Send SMS (same behavior as seniors)
        $smsDebug = null;
        try {
            $phone = $beneficiary->mobile_number ?? $beneficiary->contact_number ?? $beneficiary->phone ?? $beneficiary->cellphone ?? $beneficiary->contact_no ?? null;

            if ($phone) {
                $digits = preg_replace('/\D+/', '', (string) $phone);
                $recipient = null;

                if (preg_match('/^09\d{9}$/', $digits)) {
                    $recipient = '63' . substr($digits, 1);
                } elseif (preg_match('/^63\d{10}$/', $digits)) {
                    $recipient = $digits;
                } else {
                    Log::warning('Invalid phone for PWD beneficiary', [
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
                        $sms = new SmsService();
                        $result = $sms->sendBulkSms([$recipient], $message, $sender);

                        $status = strtolower((string)($result['status'] ?? 'error'));
                        $apiMessage = $result['message'] ?? null;
                        if (empty($apiMessage) && isset($result['raw']) && is_array($result['raw'])) {
                            $apiMessage = $result['raw']['message'] ?? json_encode(array_slice($result['raw'], 0, 3));
                        }

                        Log::info('PhilSMS PWD verification', [
                            'beneficiary_id' => $beneficiary->id,
                            'recipient'      => $recipient,
                            'sender'         => $sender,
                            'status'         => $status,
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
            Log::error('SMS send exception for PWD verification', [
                'beneficiary_id' => $beneficiary->id,
                'error' => $e->getMessage(),
            ]);
            $smsDebug = 'SMS exception: ' . $e->getMessage();
        }

        session()->flash('success', 'Beneficiary verified successfully and notification processed.');
        session()->flash('sms_debug', $smsDebug);

        return back();
    }

    public function disableBeneficiary($id)
    {
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

    public function deleteBeneficiary($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);
        $beneficiary->delete();

        return back()->with('success', 'Beneficiary deleted successfully.');
    }
}
