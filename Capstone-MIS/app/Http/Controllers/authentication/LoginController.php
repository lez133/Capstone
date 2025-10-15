<?php

namespace App\Http\Controllers\Authentication;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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

        // Attempt to log in as MSWD Member
        if (Auth::guard('web')->attempt($credentials)) {
        $request->session()->regenerate();
        $user = Auth::guard('web')->user();

        if ($user->role === 'Barangay Representative') {
            return redirect()->route('brgyrep.dashboard');
        } else {
            return redirect()->route('mswd.dashboard');
        }
    }

        // Attempt to log in as Beneficiary
        if (Auth::guard('beneficiary')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('beneficiaries.dashboard');
        }

        // If login fails for both guards
        return back()->withErrors([
            'username' => 'Invalid login credentials.',
        ]);
    }

    public function logout(Request $request)
    {
        // Check if the user is logged in as a Beneficiary
        if (Auth::guard('beneficiary')->check()) {
            Auth::guard('beneficiary')->logout();
        } else {
            // Default to logging out as an MSWD Member
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.index'); // Redirect to the login page
    }
}
