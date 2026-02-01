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

        // Try MSWD/BrgyRep guards first (same provider/model)
        if (Auth::guard('mswd')->attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::guard('mswd')->user();
            if ($user->role === 'MSWD Representative') {
                return redirect()->route('mswd.dashboard');
            } elseif ($user->role === 'Barangay Representative') {
                Auth::guard('mswd')->logout();
                if (Auth::guard('brgyrep')->attempt($credentials)) {
                    $request->session()->regenerate();
                    return redirect()->route('brgyrep.dashboard');
                }
            } else {
                Auth::guard('mswd')->logout();
                return back()->withErrors(['username' => 'Role not allowed.'])->withInput();
            }
        }

        // Try Beneficiary guard
        if (Auth::guard('beneficiary')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('beneficiaries.dashboard');
        }

        // If login fails for all guards
        return back()->withErrors([
            'username' => 'Invalid login credentials.',
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::guard('beneficiary')->check()) {
            Auth::guard('beneficiary')->logout();
        } elseif (Auth::guard('mswd')->check()) {
            Auth::guard('mswd')->logout();
        } elseif (Auth::guard('brgyrep')->check()) {
            Auth::guard('brgyrep')->logout();
        } else {
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.index');
    }
}
