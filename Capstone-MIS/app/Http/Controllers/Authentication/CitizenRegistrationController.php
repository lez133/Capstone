<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (
                        DB::table('beneficiaries')->where('email', $value)->exists() ||
                        DB::table('mswd_members')->where('email', $value)->exists()
                    ) {
                        $fail('The email address is already in use.');
                    }
                },
            ],
            'phone'            => 'required|string|max:15',
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
            'barangay_id' => 'required|exists:barangays,id', // Validate barangay_id
        ]);

        // Auto-calculate age
        $birthday = new \DateTime($request->birthday);
        $today    = new \DateTime();
        $age      = $today->diff($birthday)->y;

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
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Registration successful! Please log in.');
    }

    /**
     * Validate uniqueness (AJAX)
     */
    public function validateField(Request $request)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        if (!in_array($field, ['email', 'username'])) {
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
