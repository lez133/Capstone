<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;

class CitizenRegistrationController extends Controller
{
    /**
     * Show registration form
     */
    public function create()
    {
        $barangays = Barangay::orderBy('barangay_name', 'asc')->get();
        return view('content.authentication.register-as-citizen', compact('barangays'));
    }

    /**
     * Handle registration
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'last_name'        => 'required|string|max:255',
            'first_name'       => 'required|string|max:255',
            'middle_name'      => 'nullable|string|max:255',
            'suffix'           => 'nullable|string|max:50',
            'email'            => [
                'nullable',
                'email',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        if (
                            DB::table('beneficiaries')->where('email', $value)->exists() ||
                            DB::table('mswd_members')->where('email', $value)->exists()
                        ) {
                            $fail('The email address is already in use.');
                        }
                    }
                },
            ],
            'phone'            => [
                'required',
                'regex:/^(09\d{9}|639\d{9})$/',
                function ($attribute, $value, $fail) {
                    // Always format to 639XXXXXXXXX
                    $formatted = $value;
                    if (preg_match('/^09\d{9}$/', $value)) {
                        $formatted = '63' . substr($value, 1);
                    }
                    // Validate length and prefix
                    if (!preg_match('/^639\d{9}$/', $formatted)) {
                        $fail('Phone number must be in 639XXXXXXXXX format and valid PH mobile number.');
                    }
                    // Check uniqueness in beneficiaries (phone) and mswd_members (contact)
                    if (
                        DB::table('beneficiaries')->where('phone', $formatted)->exists() ||
                        DB::table('mswd_members')->where('contact', $formatted)->exists()
                    ) {
                        $fail('The phone number is already taken. Please use a different number.');
                    }
                },
            ],
            'beneficiary_type' => 'required|string|in:Senior Citizen,PWD,Both',
            'birthday'         => 'required|date',
            'gender'           => 'required|string|in:Male,Female',
            'civil_status'     => 'required|string|in:Single,Married,Widowed,Divorced',
            'osca_number'      => 'nullable|string|max:50',
            'pwd_id'           => 'nullable|string|max:50',
            'username'         => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (
                        DB::table('beneficiaries')->where('username', $value)->exists() ||
                        DB::table('mswd_members')->where('username', $value)->exists()
                    ) {
                        $fail('The username is already taken.');
                    }
                },
            ],
            'password'         => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Z])(?=.*\d).+$/',
                'confirmed',
            ],
            'g-recaptcha-response' => 'required|captcha',
            'barangay_id' => 'required|exists:barangays,id',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        // Auto-calculate age
        $birthday = new \DateTime($request->birthday);
        $today    = new \DateTime();
        $age      = $today->diff($birthday)->y;

        // handle avatar upload
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        // Save record
        Beneficiary::create([
            'last_name'        => $request->last_name,
            'first_name'       => $request->first_name,
            'middle_name'      => $request->middle_name,
            'suffix'           => $request->suffix,
            'email'            => $request->email,
            'phone'            => $request->phone,
            'beneficiary_type' => $request->beneficiary_type,
            'birthday'         => $request->birthday,
            'age'              => $age,
            'gender'           => $request->gender,
            'civil_status'     => $request->civil_status,
            'osca_number'      => $request->osca_number,
            'pwd_id'           => $request->pwd_id,
            'username'         => $request->username,
            'password'         => Hash::make($request->password),
            'barangay_id'      => $request->barangay_id,
            'avatar'           => $avatarPath,
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Registration successful! Please log in.');
    }

    public function validateField(Request $request)
    {
        $field = $request->input('field');
        $value = trim($request->input('value'));

        // Allow only safe columns
        $allowed = ['email', 'username', 'phone'];
        if (!in_array($field, $allowed)) {
            return response()->json(['valid' => false, 'message' => 'Invalid field.'], 400);
        }

        if ($field === 'phone') {
            // Normalize to 639XXXXXXXXX
            $formatted = $value;
            if (preg_match('/^09\d{9}$/', $value)) {
                $formatted = '63' . substr($value, 1);
            }
            if (!preg_match('/^639\d{9}$/', $formatted)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Phone number must be in 639XXXXXXXXX format and valid PH mobile number.',
                ]);
            }
            $exists = DB::table('beneficiaries')->where('phone', $formatted)->exists()
                || DB::table('mswd_members')->where('contact', $formatted)->exists();
            if ($exists) {
                return response ()->json([
                    'valid' => false,
                    'message' => 'Phone number is already taken.',
                ]);
            }
            return response()->json(['valid' => true]);
        }

        $exists = DB::table('beneficiaries')->where($field, $value)->exists()
            || DB::table('mswd_members')->where($field, $value)->exists();

        if ($exists) {
            return response()->json([
                'valid' => false,
                'message' => ucfirst($field) . ' is already taken.',
            ]);
        }

        return response()->json(['valid' => true]);
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'regex:/^(639\d{9})$/']
        ]);

        $phone = $request->phone;

        try {
            // Generate a 6-digit OTP
            $otp = random_int(100000, 999999);

            // Store OTP in cache for 5 minutes
            $cacheKey = 'otp_' . $phone;
            Cache::put($cacheKey, [
                'otp' => $otp,
                'created_at' => now()
            ], now()->addMinutes(5));

            // Send OTP via SMS (implement SmsService accordingly)
            app(\App\Services\SmsService::class)->send($phone, "Your OTP is: $otp");

            return response()->json(['success' => true, 'message' => 'OTP sent successfully.']);
        } catch (\Throwable $e) {
            Log::error('OTP send error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send OTP.'], 500);
        }
    }

    public function validateOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|regex:/^(09\d{9}|639\d{9})$/',
            'otp' => 'required|digits:6'
        ]);

        $phone = $request->phone;
        $otp = $request->otp;

        // Normalize phone number to 639XXXXXXXXX format
        if (preg_match('/^09\d{9}$/', $phone)) {
            $phone = '63' . substr($phone, 1);
        }

        // Retrieve OTP from cache
        $cacheKey = 'otp_' . $phone;
        $otpData = Cache::get($cacheKey);

        if ($otpData && isset($otpData['otp'], $otpData['created_at'])) {
            $createdAt = $otpData['created_at'];
            $isValid = $otpData['otp'] == $otp && now()->diffInMinutes($createdAt) <= 15;
            if ($isValid) {
                Cache::forget($cacheKey);
                return response()->json(['success' => true, 'message' => 'OTP validated successfully.']);
            }
        }

        return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 422);
    }
}
