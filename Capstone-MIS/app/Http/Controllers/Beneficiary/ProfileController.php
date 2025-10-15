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

class ProfileController extends Controller
{
    /**
     * Show the profile management page.
     */
    public function index()
    {
        $userId = Auth::guard('beneficiary')->id();
        $beneficiary = Beneficiary::with('barangay')->find($userId);

        // prepare display values
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
     * Update the profile information.
     */
    public function update(Request $request)
    {
        $user = Beneficiary::find(Auth::guard('beneficiary')->id());

        $request->validate([
            'username' => ['required','string','max:50', Rule::unique('beneficiaries','username')->ignore($user->id)],
            'current_password' => ['required','string'],
            'password' => ['nullable','string','min:8','confirmed'],
        ]);

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->username = $request->input('username');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }
}
