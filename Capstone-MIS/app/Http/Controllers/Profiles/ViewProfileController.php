<?php

namespace App\Http\Controllers\Profiles;

use App\Http\Controllers\Controller;
use App\Models\MSWDMember;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViewProfileController extends Controller
{
    /**
     * Display the specified MSWD Member's profile.
     *
     * @param string $encryptedId
     * @return \Illuminate\View\View
     */
    public function show($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);

        // Ensure the logged-in user can only view their own profile
        if (Auth::id() !== $id) {
            abort(403, 'Unauthorized action.');
        }

        $member = MSWDMember::findOrFail($id);
        return view('content.admin-interface.view-profiles.view-profile', compact('member'));
    }
}
