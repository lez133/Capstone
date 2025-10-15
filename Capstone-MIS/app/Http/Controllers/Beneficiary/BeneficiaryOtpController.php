<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Beneficiary;
use Illuminate\Support\Facades\Mail;

class BeneficiaryOtpController extends Controller
{
    public function show()
    {
        $authUser = Auth::guard('beneficiary')->user();
        $user = Beneficiary::find($authUser->id);

        if ($user && $user->verified && !$user->otp_confirmed && !$user->otp_code) {
            $otp = rand(100000, 999999);
            $user->otp_code = $otp;
            $user->save();

            // Send email
            Mail::to($user->email)->send(new \App\Mail\BeneficiaryOtpMail($user, $otp));
        }

        return view('content.beneficiary-interface.beneficiary-otp.otp');
    }

    public function verify(Request $request)
    {
        $authUser = Auth::guard('beneficiary')->user();

        // Validate OTP input
        $request->validate([
            'otp_code' => 'required|digits:6',
        ]);

        // Retrieve the Beneficiary model instance
        $user = Beneficiary::find($authUser->id);

        if ($user && $user->otp_code === $request->otp_code) {
            $user->otp_confirmed = true;
            $user->otp_code = null;
            $user->save();
            return redirect()->route('beneficiaries.dashboard')->with('success', 'OTP verified successfully!');
        }

        return back()->with('error', 'Invalid OTP. Please try again.');
    }

    public function resend(Request $request)
    {
        $authUser = Auth::guard('beneficiary')->user();
        $user = Beneficiary::find($authUser->id);

        if ($user && $user->verified && !$user->otp_confirmed) {
            $otp = rand(100000, 999999);
            $user->otp_code = $otp;
            $user->save();

            // Send email
            Mail::to($user->email)->send(new \App\Mail\BeneficiaryOtpMail($user, $otp));

            return back()->with('success', 'OTP has been sent to your email.');
        }

        return back()->with('error', 'Unable to send OTP.');
    }
}
