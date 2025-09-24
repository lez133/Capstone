<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CitizenRegistrationController extends Controller
{
    /**
     * Show the registration form.
     */
    public function create()
    {
        return view('content.authentication.register-as-citizen');
    }

    /**
     * Handle the registration form submission.
     */
    public function store(Request $request)
    {
        $request->validate([
            'last_name'        => 'required|string|max:255',
            'first_name'       => 'required|string|max:255',
            'middle_name'      => 'nullable|string|max:255',
            'suffix'           => 'nullable|string|max:50',
            'email'            => 'required|email|unique:beneficiaries,email',
            'phone'            => 'required|string|max:15',
            'beneficiary_type' => 'required|string|in:Senior Citizen,PWD,Both',
            'birthday'         => 'required|date',
            'gender'           => 'required|string|in:Male,Female',
            'civil_status'     => 'required|string|in:Single,Married,Widowed',
            'osca_number'      => 'nullable|string|max:50',
            'pwd_id'           => 'nullable|string|max:50',
            'password'         => 'required|string|min:8|confirmed',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        // Auto-calculate age from birthday
        $birthday = new \DateTime($request->birthday);
        $today    = new \DateTime();
        $age      = $today->diff($birthday)->y;

        // Create the beneficiary record
        $beneficiary = Beneficiary::create([
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
            'password'         => Hash::make($request->password), // secure password hash
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Registration successful! Please log in.');
    }
}
