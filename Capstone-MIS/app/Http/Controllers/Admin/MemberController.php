<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MSWDMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

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
        return view('content.admin-interface.member.add-official-member.add-member');
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
                    'required',
                    'email',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        // Check email in mswd_members and beneficiaries tables
                        if (DB::table('mswd_members')->where('email', $value)->exists() ||
                            DB::table('beneficiaries')->where('email', $value)->exists()) {
                            $fail('The email address is already in use. Please use a different email.');
                        }
                    },
                ],
                'contact' => 'required|string|max:15',
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
            ]);

            // Validate the birthdate
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
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($id) {
                    // Check username in mswd_members and beneficiaries tables, excluding the current record
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
        $value = strtolower(trim($request->input('value')));

        if (!in_array($field, ['email', 'username'])) {
            return response()->json(['valid' => true]);
        }

        $exists = DB::table('beneficiaries')
            ->whereRaw('LOWER(' . $field . ') = ?', [$value])
            ->exists()
            || DB::table('mswd_members')
            ->whereRaw('LOWER(' . $field . ') = ?', [$value])
            ->exists();

        if ($exists) {
            return response()->json([
                'valid' => false,
                'message' => ucfirst($field) . ' is already taken.',
            ]);
        }

        return response()->json(['valid' => true]);
    }

}
