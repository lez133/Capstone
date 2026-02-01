<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class AdminSettingsController extends Controller
{
    public function index()
    {
        return view('content.admin-interface.settings.profile-settings');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', // At least one lowercase, one uppercase, one digit
                'confirmed'
            ],
        ], [
            'new_password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
        ]);

        $user = auth('mswd')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $newPasswordHash = Hash::make($request->new_password);
        if ($user instanceof Model) {
            $user->password = $newPasswordHash;
            $user->save();
        } else {
            $userId = null;
            if (is_object($user)) {
                if (isset($user->id)) {
                    $userId = $user->id;
                } elseif (method_exists($user, 'getAuthIdentifier')) {
                    $userId = $user->getAuthIdentifier();
                }
            }
            if ($userId !== null) {
                DB::table('users')->where('id', $userId)->update(['password' => $newPasswordHash]);
            } else {
                return back()->withErrors(['new_password' => 'Unable to update password for current user.']);
            }
        }

        return back()->with('success', 'Password updated successfully.');
    }
}
