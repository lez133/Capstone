<?php

namespace App\Http\Controllers\BrgyRepresentative;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MSWDMember;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ViewRepProfileController extends Controller
{
    public function show($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $representative = MSWDMember::where('role', 'Barangay Representative')->findOrFail($id);

        return view('content.brgyrepresentative-interface.view-profiles.view-profile', [
            'representative' => $representative,
        ]);
    }

    public function passwordSettings($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $representative = MSWDMember::findOrFail($id);
        return view('content.brgyrepresentative-interface.view-profiles.password-settings', [
            'representative' => $representative,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'confirmed'
            ],
        ]);

        // Ensure we work with the MSWDMember Eloquent model instance so save() is available.
        $userId = Auth::guard('brgyrep')->id();
        $user = MSWDMember::findOrFail($userId);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }

    public function edit($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $representative = MSWDMember::findOrFail($id);

        return view('content.brgyrepresentative-interface.view-profiles.edit-profile', [
            'representative' => $representative,
        ]);
    }

    public function update(Request $request, $encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);
        $representative = MSWDMember::findOrFail($id);

        $request->validate([
            'fname' => 'required|string|max:255',
            'mname' => 'nullable|string|max:255',
            'lname' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'birthday' => 'required|date',
            'contact' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:mswd_members,email,' . $representative->id,
            'username' => 'required|string|max:255|unique:mswd_members,username,' . $representative->id,
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $representative->fname = $request->fname;
        $representative->mname = $request->mname;
        $representative->lname = $request->lname;
        $representative->gender = $request->gender;
        $representative->birthday = $request->birthday;
        $representative->contact = $request->contact;
        $representative->email = $request->email;
        $representative->username = $request->username;

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('profile_pictures', $filename, 'public');
            $representative->profile_picture = $path;
        }

        $representative->save();

        return redirect()
            ->route('brgyrep.profile.view', ['encryptedId' => $encryptedId])
            ->with('success', 'Profile updated successfully.');
    }
}
