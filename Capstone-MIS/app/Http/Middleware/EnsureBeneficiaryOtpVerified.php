<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\BeneficiaryOtpMail;
use App\Models\Beneficiary;

class EnsureBeneficiaryOtpVerified
{
    public function handle($request, Closure $next)
    {
        $user = Auth::guard('beneficiary')->user();

        // Ensure $user is an Eloquent model instance
        if ($user && !($user instanceof Beneficiary)) {
            $user = Beneficiary::find($user->id);
        }

        if ($user && $user->verified && !$user->otp_confirmed) {
            // Generate OTP if not already set
            if (!$user->otp_code) {
                $otp = rand(100000, 999999);
                $user->otp_code = $otp;
                $user->save();

                // Send email
                Mail::to($user->email)->send(new BeneficiaryOtpMail($user, $otp));
            }

            // Redirect to OTP input page
            return redirect()->route('beneficiary.otp');
        }

        return $next($request);
    }
}
