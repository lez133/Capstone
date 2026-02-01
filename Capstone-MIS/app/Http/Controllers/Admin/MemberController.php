<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MSWDMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Beneficiary;
use App\Models\Barangay;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = MSWDMember::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('fname', 'LIKE', "%$search%")
                  ->orWhere('lname', 'LIKE', "%$search%")
                  ->orWhere('mname', 'LIKE', "%$search%")
                  ->orWhere('email', 'LIKE', "%$search%")
                  ->orWhere('contact', 'LIKE', "%$search%");
        }

        $members = $query->get();

        return view('content.admin-interface.member.members', compact('members'));
    }

    public function mswdMembers()
    {
        $mswdMembers = MSWDMember::where('role', 'MSWD Representative')->get();
        return view('content.admin-interface.member.mswd-members', compact('mswdMembers'));
    }

    public function brgyReps()
    {
        $brgyReps = MSWDMember::where('role', 'Barangay Representative')->get();
        return view('content.admin-interface.member.brgy-representatives', compact('brgyReps'));
    }

    public function create()
    {
        $barangays = Barangay::orderBy('barangay_name')->get();
        return view('content.admin-interface.member.add-official-member.add-member', compact('barangays'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'fname' => 'required|string|max:255',
                'mname' => 'nullable|string|max:255',
                'lname' => 'required|string|max:255',
                'birth_day' => 'required|integer|min:1|max:31',
                'birth_month' => 'required|integer|min:1|max:12',
                'birth_year' => 'required|integer|min:1900|max:' . date('Y'),
                'gender' => 'required|string|in:Male,Female,Other',
                'role' => 'required|string|in:MSWD Representative,Barangay Representative',
                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        if ($value) {
                            if (
                                DB::table('mswd_members')->where('email', $value)->exists() ||
                                DB::table('beneficiaries')->where('email', $value)->exists()
                            ) {
                                $fail('The email address is already in use. Please use a different email.');
                            }
                        }
                    },
                ],
                'contact' => [
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
                            $fail('Contact number must be in 639XXXXXXXXX format and valid PH mobile number.');
                        }
                        // Check uniqueness in mswd_members and beneficiaries (use phone for beneficiaries)
                        if (
                            DB::table('mswd_members')->where('contact', $formatted)->exists() ||
                            DB::table('beneficiaries')->where('phone', $formatted)->exists()
                        ) {
                            $fail('The contact number is already taken. Please use a different number.');
                        }
                    },
                ],
                'username' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        // Check username in mswd_members and beneficiaries tables
                        if (DB::table('mswd_members')->where('username', $value)->exists() ||
                            DB::table('beneficiaries')->where('username', $value)->exists()) {
                            $fail('The username is already taken. Please choose a different one.');
                        }
                    },
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                ],
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'barangay_id' => 'nullable|exists:barangays,id',
            ]);

            // require barangay when role is Barangay Representative
            if ($request->role === 'Barangay Representative' && empty($request->barangay_id)) {
                return back()->withErrors(['barangay_id' => 'Barangay is required for Barangay Representative.'])->withInput();
            }

            // Validate birthdate
            $day = $request->birth_day;
            $month = $request->birth_month;
            $year = $request->birth_year;
            if (!checkdate($month, $day, $year)) {
                return back()->withErrors(['birthday' => 'The selected date is invalid.'])->withInput();
            }

            $validated['birthday'] = "$year-$month-$day";
            $validated['password'] = Hash::make($request->password);

            if ($request->hasFile('profile_picture')) {
                $validated['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
            }

            $validated['created_by'] = Auth::user()->id;

            if ($request->filled('barangay_id')) {
                $validated['barangay_id'] = $request->barangay_id;
            }

            // Format contact to 639XXXXXXXXX before saving
            $contact = $request->contact;
            if (preg_match('/^09\d{9}$/', $contact)) {
                $contact = '63' . substr($contact, 1);
            }
            $validated['contact'] = $contact;

            MSWDMember::create($validated);

            return redirect()->route('members.index')->with('success', 'Member added successfully.');
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
            return back()->withErrors($errors)->withInput();
        }
    }

    public function show($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $member = MSWDMember::with('creator')->findOrFail($id);
        return view('content.admin-interface.member.View-member.View-Member', compact('member'));
    }

    public function edit($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $member = MSWDMember::findOrFail($id);
        return view('content.admin-interface.member.Edit-member.Edit-member', compact('member'));
    }

    public function update(Request $request, $encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);

        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) use ($id) {
                    // Check email in mswd_members and beneficiaries tables, excluding the current record
                    $emailExistsInMSWD = DB::table('mswd_members')
                        ->where('email', $value)
                        ->where('id', '!=', $id)
                        ->exists();
                    $emailExistsInBeneficiaries = DB::table('beneficiaries')
                        ->where('email', $value)
                        ->exists();

                    if ($emailExistsInMSWD || $emailExistsInBeneficiaries) {
                        $fail('The email address is already in use. Please use a different email.');
                    }
                },
            ],
            'contact' => 'required|string|max:15',
            'role' => 'required|string|in:MSWD Representative,Barangay Representative',
            'username' => [
                'sometimes',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($id) {
                    $usernameExistsInMSWD = DB::table('mswd_members')
                        ->where('username', $value)
                        ->where('id', '!=', $id)
                        ->exists();
                    $usernameExistsInBeneficiaries = DB::table('beneficiaries')
                        ->where('username', $value)
                        ->exists();

                    if ($usernameExistsInMSWD || $usernameExistsInBeneficiaries) {
                        $fail('The username is already taken. Please choose a different one.');
                    }
                },
            ],
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $member = MSWDMember::findOrFail($id);
        $member->update($validated);

        if ($request->hasFile('profile_picture')) {
            $validated['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
            $member->profile_picture = $validated['profile_picture'];
            $member->save();
        }

        return redirect()->route('members.show', Crypt::encrypt($member->id))
                         ->with('success', 'Member updated successfully.');
    }

    public function validateField(Request $request)
    {
        $field = $request->input('field');
        $value = trim($request->input('value'));

        if ($field === 'contact') {
            // Always convert to 639XXXXXXXXX
            $formatted = $value;
            if (preg_match('/^09\d{9}$/', $value)) {
                $formatted = '63' . substr($value, 1);
            }
            // Only check 639XXXXXXXXX format
            if (!preg_match('/^639\d{9}$/', $formatted)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Contact number must be in 639XXXXXXXXX format and valid PH mobile number.',
                ]);
            }
            $exists = MSWDMember::where('contact', $formatted)->exists() ||
                      Beneficiary::where('phone', $formatted)->exists(); // <-- FIXED HERE
            if ($exists) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Contact number is already taken.',
                ]);
            }
            return response()->json(['valid' => true]);
        }

        // Allow only specific columns
        $allowed = ['email', 'username', 'contact'];
        if (!in_array($field, $allowed)) {
            return response()->json(['valid' => false, 'message' => 'Invalid field.'], 400);
        }

        $exists = false;

        if ($field === 'email') {
            $exists = Beneficiary::where('email', $value)->exists()
                || MSWDMember::where('email', $value)->exists();
        } elseif ($field === 'username') {
            $exists = Beneficiary::where('username', $value)->exists()
                || MSWDMember::where('username', $value)->exists();
        }

        if ($exists) {
            return response()->json([
                'valid' => false,
                'message' => ucfirst($field) . ' is already taken.',
            ]);
        }

        return response()->json(['valid' => true]);
    }

    public function destroy($encryptedId)
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            $member = MSWDMember::findOrFail($id);
            $role = $member->role;
            $member->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'role' => $role
                ]);
            }

            if ($role === 'MSWD Representative') {
                return redirect()->route('members.mswd')->with('success', 'MSWD member deleted successfully.');
            } elseif ($role === 'Barangay Representative') {
                return redirect()->route('members.brgy')->with('success', 'Barangay Representative deleted successfully.');
            }
        } catch (\Throwable $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Failed to delete member.'], 500);
            }
            return redirect()->route('members.index')->with('error', 'Failed to delete member.');
        }
    }
}
