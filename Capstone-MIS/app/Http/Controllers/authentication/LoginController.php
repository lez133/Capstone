<?php

namespace App\Http\Controllers\authentication;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\MSWDMember;

class LoginController extends Controller
{
    public function index()
    {
        return view('content.authentication.login-interface');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $member = MSWDMember::where('username', $credentials['username'])->first();

        if (!$member) {
            return back()->withErrors(['username' => 'Username not found.']);
        }

        if ($member && Hash::check($credentials['password'], $member->password)) {

            Auth::login($member);

            if ($member->role === 'MSWD Representative') {
                return redirect()->route('mswd.dashboard'); // Redirect to MSWD interface
            } elseif ($member->role === 'Barangay Representative') {
                return redirect()->route('brgyrep.dashboard'); // Redirect to Barangay Representative interface
            }
        }

        return back()->withErrors([
            'username' => 'Invalid login credentials.', // Error message for invalid credentials
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.index');
    }
}
