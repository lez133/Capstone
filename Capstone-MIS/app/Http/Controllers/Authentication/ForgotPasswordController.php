<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Services\SmsService;

class ForgotPasswordController extends Controller
{
    protected SmsService $sms;
    protected int $maxAttempts = 5;
    protected int $decaySeconds = 3600; // 1 hour

    public function __construct(SmsService $sms)
    {
        $this->sms = $sms;
    }

    // show the dedicated forgot-password page
    public function show()
    {
        return view('content.authentication.forgot-password');
    }

    // handle POST from form (email or phone)
    public function sendReset(Request $request)
    {
        $request->validate(['identifier' => 'required|string|max:255']);
        $identifier = trim($request->input('identifier'));
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

        // masked debug identifier for logs/flash (no PII)
        $masked = (strlen($identifier) > 4) ? '***' . substr($identifier, -4) : $identifier;
        $key = 'pw-reset|' . $request->ip() . '|' . sha1($identifier);

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            Log::warning('Too many password reset attempts', ['ip' => $request->ip(), 'id_mask' => $masked]);
            return redirect()->route('auth.password.request')
                ->with('status', 'If an account exists, reset instructions have been sent.')
                ->with('fp_debug', 'Too many attempts — try again later.');
        }
        RateLimiter::hit($key, $this->decaySeconds);

        Log::info('Forgot password requested', ['identifier_masked' => $masked, 'is_email' => (bool)$isEmail]);

        // Email flow: use Laravel password broker (secure)
        if ($isEmail) {
            // do not reveal result to user
            try {
                Password::sendResetLink(['email' => $identifier]);
            } catch (\Throwable $e) {
                Log::error('Password broker error', ['err' => $e->getMessage(), 'email_mask' => $masked]);
                // still return generic response
            }

            return redirect()->route('auth.password.request')
                ->with('status', 'If an account exists, reset instructions have been sent.')
                ->with('fp_debug', 'If an account exists, an email reset was attempted (masked).');
        }

        // Phone flow: normalize phone number
        $phone = preg_replace('/[^0-9\+]/', '', $identifier);

        // locate user record in users or beneficiaries tables (check possible phone columns)
        $user = null;
        $isBeneficiary = false;

        // search users table
        if (Schema::hasTable('users')) {
            $phoneCols = ['phone','mobile','mobile_number','contact_no','contact_number'];
            $available = array_filter($phoneCols, fn($c) => Schema::hasColumn('users', $c));
            if (!empty($available)) {
                $q = DB::table('users');
                $q->where(function($w) use ($available, $phone) {
                    foreach ($available as $col) $w->orWhere($col, $phone);
                });
                $user = $q->first();
            }
        }

        // fallback to beneficiaries table
        if (!$user && Schema::hasTable('beneficiaries')) {
            $benefPhoneCols = ['phone','mobile','mobile_number','contact_no','contact_number'];
            $availB = array_filter($benefPhoneCols, fn($c) => Schema::hasColumn('beneficiaries', $c));
            if (!empty($availB)) {
                $bq = DB::table('beneficiaries');
                $bq->where(function($w) use ($availB, $phone) {
                    foreach ($availB as $col) $w->orWhere($col, $phone);
                });
                $user = $bq->first();
                if ($user) $isBeneficiary = true;
            }
        }

        if (! $user) {
            Log::info('No account matched identifier', ['phone_ending' => substr($phone, -4)]);
            return redirect()->route('auth.password.request')
                ->with('status', 'If an account exists, reset instructions have been sent.')
                ->with('fp_debug', 'No account matched the provided identifier (masked).');
        }

        // ensure password_resets table exists
        if (!Schema::hasTable('password_resets')) {
            Log::error('password_resets table missing');
            return redirect()->route('auth.password.request')
                ->with('status', 'Password reset service temporarily unavailable.')
                ->with('fp_debug', 'password_resets table missing. Run migrations.');
        }

        // create and store hashed 6-digit code
        $code = random_int(100000, 999999);
        DB::table('password_resets')->insert([
            'email' => 'phone:' . $phone,
            'token' => Hash::make((string)$code),
            'created_at' => Carbon::now(),
        ]);
        Log::debug('password_resets entry inserted (masked)', ['phone_ending' => substr($phone, -4)]);

        // send SMS (use SmsService) — do not include PII beyond code; log masked only
        $message = "Your password reset code is: {$code}. It expires in 60 minutes.";
        try {
            if (is_callable([$this->sms, 'sendSms'])) {
                call_user_func([$this->sms, 'sendSms'], $phone, $message);
            } elseif (is_callable([$this->sms, 'sendBulkSms'])) {
                call_user_func([$this->sms, 'sendBulkSms'], [$phone], $message, 'MSWDInfo');
            } else {
                Log::warning('SmsService has no known send method');
            }
            Log::info('Reset SMS attempted', ['phone_ending' => substr($phone, -4)]);
        } catch (\Throwable $e) {
            Log::error('SMS send failed', ['err' => $e->getMessage(), 'phone_ending' => substr($phone, -4)]);
            return redirect()->route('auth.password.request')
                ->with('status', 'If an account exists, reset instructions have been sent.')
                ->with('fp_debug', 'SMS send failed — check server logs (masked).');
        }

        // optional email fallback if record has email
        if (!empty($user->email ?? null)) {
            try {
                Mail::raw($message, function($m) use ($user) {
                    $m->to($user->email)->subject('Password reset code');
                });
            } catch (\Throwable $e) {
                Log::warning('Email fallback failed', ['err' => $e->getMessage(), 'email_mask' => (strlen($user->email)>4 ? '***'.substr($user->email,-4) : $user->email)]);
            }
        }

        return redirect()->route('auth.password.request')
            ->with('status', 'If an account exists, reset instructions have been sent.')
            ->with('fp_debug', 'A reset code was sent (masked).');
    }
}
