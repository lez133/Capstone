<?php

namespace App\Http\Controllers\BrgyRepresentative;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssistRegistrationController extends Controller
{
    // Show the assist registration form
    public function create()
    {
        $barangays = Barangay::orderBy('barangay_name')->get();
        return view('content.brgyrepresentative-interface.assist-registration.assist-registrations', compact('barangays'));
    }

    // Store the assisted registration
    public function store(Request $request)
    {
        $validated = $request->validate([
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
                    $formatted = $value;
                    if (preg_match('/^09\d{9}$/', $value)) {
                        $formatted = '63' . substr($value, 1);
                    }
                    if (!preg_match('/^639\d{9}$/', $formatted)) {
                        $fail('Phone number must be in 639XXXXXXXXX format and valid PH mobile number.');
                    }
                    if (
                        DB::table('beneficiaries')->where('phone', $formatted)->exists() ||
                        DB::table('mswd_members')->where('contact', $formatted)->exists()
                    ) {
                        $fail('The phone number is already taken. Please use a different number.');
                    }
                },
            ],
            'beneficiary_type' => 'required|string|in:Senior Citizen,PWD',
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
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'confirmed',
            ],
            'barangay_id'      => 'required|exists:barangays,id',
            'avatar'           => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            $birthday = new \DateTime($request->birthday);
            $today = new \DateTime();
            $age = $today->diff($birthday)->y;

            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

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
                'assisted_by'      => Auth::id(),
            ]);

            return redirect()->route('brgyrep.dashboard')->with('success', 'Beneficiary registered successfully.');
        } catch (\Exception $e) {
            return redirect()->route('brgyrep.dashboard')->with('error', 'Failed to register beneficiary. Please try again.');
        }
    }

    public function validateField(Request $request)
    {
        $field = $request->input('field');
        $value = trim($request->input('value'));

        $allowed = ['email', 'username', 'phone'];
        if (!in_array($field, $allowed)) {
            return response()->json(['valid' => false, 'message' => 'Invalid field.'], 400);
        }

        if ($field === 'phone') {
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
                return response()->json([
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
}
