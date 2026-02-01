<?php
namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\Beneficiary;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{

    public function index()
    {
        $userId = Auth::guard('beneficiary')->id();
        $beneficiary = Beneficiary::with('barangay')->find($userId);

        $barangayName = optional($beneficiary->barangay)->barangay_name ?? 'N/A';

        $osca = $beneficiary->osca_number ?? null;
        $oscaDecrypted = null;
        if ($osca) {
            try {
                $oscaDecrypted = Crypt::decrypt($osca);
            } catch (\Exception $e) {
                $oscaDecrypted = $osca;
            }
        }

        $pwdId = $beneficiary->pwd_id ?? 'N/A';
        $beneficiaryType = $beneficiary->beneficiary_type ?? 'N/A';
        $phone = $beneficiary->phone ?? 'N/A';
        $birthday = $beneficiary->birthday ? Carbon::parse($beneficiary->birthday)->format('M d, Y') : 'N/A';
        $age = $beneficiary->age ?? 'N/A';

        // Logic for OSCA/PWD label and value
        $typeLower = strtolower($beneficiaryType);
        if (str_contains($typeLower, 'senior') && str_contains($typeLower, 'pwd')) {
            $oscaPwdLabel = 'OSCA Number / PWD ID';
            $oscaPwdValue = ($oscaDecrypted ?? 'N/A') . ' / ' . ($pwdId ?? 'N/A');
        } elseif (str_contains($typeLower, 'senior')) {
            $oscaPwdLabel = 'OSCA Number';
            $oscaPwdValue = $oscaDecrypted ?? 'N/A';
        } elseif (str_contains($typeLower, 'pwd')) {
            $oscaPwdLabel = 'PWD ID';
            $oscaPwdValue = $pwdId ?? 'N/A';
        } else {
            $oscaPwdLabel = 'OSCA / PWD ID';
            $oscaPwdValue = ($oscaDecrypted ?? 'N/A') . ($pwdId ? ' / ' . $pwdId : '');
        }

        return view('content.beneficiary-interface.profile.profile-management', compact(
            'beneficiary',
            'barangayName',
            'oscaPwdLabel',
            'oscaPwdValue',
            'beneficiaryType',
            'phone',
            'birthday',
            'age'
        ));
    }

    /**
     * Update basic profile fields (name, phone, birthday).
     */
    public function update(Request $request)
    {
        $user = Beneficiary::findOrFail(Auth::guard('beneficiary')->id());

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'phone'      => ['nullable','string','max:20'],
            'birthday'   => ['nullable','date'],
        ]);

        // normalize phone (optional): keep as-is or normalize to 639...
        $phone = trim($validated['phone'] ?? '');
        if ($phone) {
            // attempted normalization: 09XXXXXXXXX -> 639XXXXXXXXX
            if (preg_match('/^09\d{9}$/', $phone)) {
                $phone = '63' . substr($phone, 1);
            }
        } else {
            $phone = null;
        }

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->phone = $phone;
        $user->birthday = $validated['birthday'] ?? null;

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Change password (requires current password).
     */
    public function updatePassword(Request $request)
    {
        $user = Beneficiary::findOrFail(Auth::guard('beneficiary')->id());

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        return back()->with('success', 'Password changed successfully.');
    }

    public function uploadAvatar(Request $request)
    {
        $user = Beneficiary::findOrFail(Auth::guard('beneficiary')->id());

        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $file = $request->file('avatar');
        $path = $file->store('avatars', 'public');

        // delete old avatar if exists
        if ($user->avatar) {
            try {
                Storage::disk('public')->delete($user->avatar);
            } catch (\Throwable $e) {
                // ignore deletion errors
            }
        }

        $user->avatar = $path;
        $user->save();

        return back()->with('success', 'Avatar updated.');
    }

    public function resetAvatar(Request $request)
    {
        $user = Beneficiary::findOrFail(Auth::guard('beneficiary')->id());

        if ($user->avatar) {
            try {
                Storage::disk('public')->delete($user->avatar);
            } catch (\Throwable $e) {
                // ignore deletion errors
            }
        }

        $user->avatar = null;
        $user->save();

        return back()->with('success', 'Avatar reset to default.');
    }
}
