<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MSWDMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

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
        return view('content.admin-interface.member.add-official-member.add-member'); // Display the add member form
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
                'email' => 'required|email|unique:mswd_members,email',
                'contact' => 'required|string|max:15',
                'username' => 'required|string|unique:mswd_members,username|max:255',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                ],
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $day = $request->birth_day;
            $month = $request->birth_month;
            $year = $request->birth_year;

            if (!checkdate($month, $day, $year)) {
                return back()->withErrors(['birthday' => 'The selected date is invalid.'])->withInput();
            }

            $validated['birthday'] = "$year-$month-$day"; // Store as YYYY-MM-DD format
            $validated['password'] = Hash::make($request->password);

            if ($request->hasFile('profile_picture')) {
                $validated['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
            }
            $validated['created_by'] = Auth::user()->id;

            MSWDMember::create($validated);

            return redirect()->route('members.index')->with('success', 'Member added successfully.');
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();

            if ($errors->has('username')) {
                $errors->add('username', 'The username is already taken. Please choose a different one.');
            }

            if ($errors->has('email')) {
                $errors->add('email', 'The email address is already in use. Please use a different email.');
            }

            return back()->withErrors($errors)->withInput();
        }
    }
    public function show($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId); // Decrypt the ID
        $member = MSWDMember::with('creator')->findOrFail($id);
        return view('content.admin-interface.member.View-member.View-Member', compact('member'));
    }
    public function edit($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId); // Decrypt the ID
        $member = MSWDMember::findOrFail($id);
        return view('content.admin-interface.member.Edit-member.Edit-member', compact('member'));
    }
    public function update(Request $request, $encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|unique:mswd_members,email,' . $id,
            'contact' => 'required|string|max:15',
            'role' => 'required|string|in:MSWD Representative,Barangay Representative',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $member = MSWDMember::findOrFail($id);

        $member->update($validated);

        if ($request->hasFile('profile_picture')) {
            $validated['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
            $member->profile_picture = $validated['profile_picture'];
            $member->save();
        }

        return redirect()->route('members.show', Crypt::encrypt($member->id))->with('success', 'Member updated successfully.');
    }

    public function validateField(Request $request)
    {
        $field = $request->field;
        $value = $request->value;

        $rules = [
            'username' => 'unique:mswd_members,username',
            'email' => 'unique:mswd_members,email',
        ];

        $validator = Validator::make([$field => $value], [$field => $rules[$field]]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => $validator->errors()->first($field),
            ]);
        }

        return response()->json(['valid' => true]);
    }
}
