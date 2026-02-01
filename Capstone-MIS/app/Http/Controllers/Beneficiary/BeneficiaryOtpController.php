<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Beneficiary;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\SmsService;
use Carbon\Carbon; // <-- added

class BeneficiaryOtpController extends Controller
{
    public function show(Request $request)
    {
        $authUser = Auth::guard('beneficiary')->user();
        $user = Beneficiary::find($authUser->id);

        // method can be 'email' or 'sms' via query string ?method=sms
        $method = $request->query('method', 'email');

        if ($user && $user->verified && !$user->otp_confirmed && !$user->otp_code) {
            $otp = rand(100000, 999999);
            $user->otp_code = $otp;
            $user->otp_created_at = now(); // set creation time
            $user->save();

            if ($method === 'sms') {
                // attempt SMS send
                try {
                    $sms = new SmsService();
                    // pick phone field(s)
                    $raw = $user->mobile_number ?? $user->contact_number ?? $user->phone ?? $user->cellphone ?? $user->contact_no ?? null;
                    $digits = $raw ? preg_replace('/\D+/', '', $raw) : null;
                    $recipient = null;
                    if ($digits && preg_match('/^09\d{9}$/', $digits)) {
                        $recipient = '63' . substr($digits, 1);
                    } elseif ($digits && preg_match('/^63\d{9,10}$/', $digits)) {
                        $recipient = $digits;
                    }

                    if ($recipient) {
                        $message = "Your MSWD OTP code is: {$otp}. Do not share this code with anyone.";
                        $sender = config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', 'PhilSMS'));
                        $result = $sms->sendBulkSms([$recipient], $message, $sender);
                        Log::info('OTP SMS attempt', ['beneficiary_id' => $user->id, 'recipient' => $recipient, 'result' => $result]);
                        if (($result['status'] ?? '') === 'success') {
                            session()->flash('success', 'OTP sent via SMS.');
                        } else {
                            $msg = $result['message'] ?? json_encode($result['raw'] ?? $result);
                            session()->flash('error', 'Failed to send OTP via SMS: ' . (is_string($msg) ? $msg : json_encode($msg)));
                        }
                    } else {
                        session()->flash('error', 'No valid phone number available to send OTP via SMS.');
                    }
                } catch (\Throwable $e) {
                    Log::error('OTP SMS exception', ['beneficiary_id' => $user->id, 'error' => $e->getMessage()]);
                    session()->flash('error', 'Exception sending OTP via SMS: ' . $e->getMessage());
                }
            } else {
                // default: email
                try {
                    if (!empty($user->email)) {
                        Mail::to($user->email)->send(new \App\Mail\BeneficiaryOtpMail($user, $otp));
                        session()->flash('success', 'OTP sent to your email.');
                    } else {
                        // avoid "An email must have a 'To', 'Cc', or 'Bcc' header." error
                        \Illuminate\Support\Facades\Log::warning('OTP not sent: beneficiary has no email', ['beneficiary_id' => $user->id]);
                        session()->flash('error', 'No email address on file to send OTP.');
                    }
                } catch (\Throwable $e) {
                    Log::warning('OTP email failed', ['beneficiary_id' => $user->id, 'error' => $e->getMessage()]);
                    session()->flash('error', 'Unable to send OTP to email.');
                }
            }
        }

        return view('content.beneficiary-interface.beneficiary-otp.otp');
    }

    public function verify(Request $request)
    {
        $authUser = Auth::guard('beneficiary')->user();

        $request->validate([
            'otp_code' => 'required|digits:6',
        ]);

        $user = Beneficiary::find($authUser->id);

        // check expiration (15 minutes)
        if ($user && $user->otp_created_at) {
            $expiresAt = Carbon::parse($user->otp_created_at)->addMinutes(15);
            if (Carbon::now()->greaterThan($expiresAt)) {
                // invalidate stored OTP
                $user->otp_code = null;
                $user->otp_created_at = null;
                $user->save();
                return back()->with('error', 'OTP has expired. Please request a new one.');
            }
        }

        if ($user && $user->otp_code === $request->otp_code) {
            $user->otp_confirmed = true;
            $user->otp_code = null;
            $user->otp_created_at = null;
            $user->save();
            return redirect()->route('beneficiaries.dashboard')->with('success', 'OTP verified successfully!');
        }

        return back()->with('error', 'Invalid OTP. Please try again.');
    }

    public function resend(Request $request)
    {
        $authUser = Auth::guard('beneficiary')->user();
        $user = Beneficiary::find($authUser->id);

        $method = $request->input('method', 'email'); // 'email' or 'sms'

        if ($user && $user->verified && !$user->otp_confirmed) {
            $otp = rand(100000, 999999);
            $user->otp_code = $otp;
            $user->otp_created_at = now(); // update creation time on resend
            $user->save();

            if ($method === 'sms') {
                try {
                    $raw = $user->mobile_number ?? $user->contact_number ?? $user->phone ?? $user->cellphone ?? $user->contact_no ?? null;
                    $digits = $raw ? preg_replace('/\D+/', '', $raw) : null;
                    $recipient = null;
                    if ($digits && preg_match('/^09\d{9}$/', $digits)) {
                        $recipient = '63' . substr($digits, 1);
                    } elseif ($digits && preg_match('/^63\d{9,10}$/', $digits)) {
                        $recipient = $digits;
                    }

                    if ($recipient) {
                        $sms = new SmsService();
                        $message = "Your MSWD OTP code is: {$otp}. Do not share this code with anyone.";
                        $sender = config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', 'PhilSMS'));
                        $result = $sms->sendBulkSms([$recipient], $message, $sender);
                        Log::info('Resend OTP SMS', ['beneficiary_id' => $user->id, 'recipient' => $recipient, 'result' => $result]);
                        if (($result['status'] ?? '') === 'success') {
                            return back()->with('success', 'OTP has been sent via SMS.');
                        } else {
                            $msg = $result['message'] ?? json_encode($result['raw'] ?? $result);
                            return back()->with('error', 'Failed to send OTP via SMS: ' . (is_string($msg) ? $msg : json_encode($msg)));
                        }
                    } else {
                        return back()->with('error', 'No valid phone number to send OTP via SMS.');
                    }
                } catch (\Throwable $e) {
                    Log::error('Resend OTP SMS exception', ['beneficiary_id' => $user->id, 'error' => $e->getMessage()]);
                    return back()->with('error', 'Exception sending OTP via SMS.');
                }
            } else {
                // email path
                try {
                    if (!empty($user->email)) {
                        Mail::to($user->email)->send(new \App\Mail\BeneficiaryOtpMail($user, $otp));
                        return back()->with('success', 'OTP has been sent to your email.');
                    } else {
                        \Illuminate\Support\Facades\Log::warning('Resend OTP skipped - no email', ['beneficiary_id' => $user->id]);
                        return back()->with('error', 'No email address available to send OTP.');
                    }
                } catch (\Throwable $e) {
                    Log::warning('Resend OTP email failed', ['beneficiary_id' => $user->id, 'error' => $e->getMessage()]);
                    return back()->with('error', 'Unable to send OTP to email.');
                }
            }
        }

        return back()->with('error', 'Unable to send OTP.');
    }
}
