<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Beneficiary;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use App\Mail\BeneficiaryVerified;
use App\Mail\BeneficiaryDisabled;
use Illuminate\Support\Facades\Mail;

class RegisteredSeniorCitizenController extends Controller
{

    public function index()
    {
        $notVerifiedCount = Beneficiary::where('beneficiary_type', 'Senior Citizen')
            ->where('verified', false)
            ->count();

        return view('content.admin-interface.beneficiaries.senior-citizen.manage-senior-citizens', compact('notVerifiedCount'));
    }
    /**
     * Display the barangay selection page.
     */
    public function barangaySelection()
    {
        // Fetch all barangays
        $barangays = Barangay::all();

        return view('content.admin-interface.beneficiaries.senior-citizen.registered-senior-barangay-selection', compact('barangays'));
    }

    /**
     * Display the registered senior citizens for a specific barangay.
     */
    public function viewSeniorCitizens($barangayId)
    {
        // Decrypt the barangay ID
        $barangayId = Crypt::decrypt($barangayId);

        // Fetch the barangay and its senior citizen beneficiaries
        $barangay = Barangay::findOrFail($barangayId);
        $seniorCitizens = Beneficiary::where('barangay_id', $barangayId)
            ->where('beneficiary_type', 'Senior Citizen')
            ->get();

        return view('content.admin-interface.beneficiaries.senior-citizen.registered-senior-citizens', compact('barangay', 'seniorCitizens'));
    }


    public function manageSeniorCitizens($encryptedBarangayId)
    {
        try {
            $barangayId = Crypt::decrypt($encryptedBarangayId);
            $barangay = Barangay::findOrFail($barangayId);

            $verifiedCitizens = Beneficiary::where('barangay_id', $barangayId)
                ->where('beneficiary_type', 'Senior Citizen')
                ->where('verified', true)
                ->get();

            $notVerifiedCitizens = Beneficiary::where('barangay_id', $barangayId)
                ->where('beneficiary_type', 'Senior Citizen')
                ->where('verified', false)
                ->get();

            $notVerifiedCount = $notVerifiedCitizens->count();

            // Pass the encrypted id so views can build links
            return view(
                'content.admin-interface.beneficiaries.senior-citizen.manage-senior-citizens',
                compact('barangay', 'verifiedCitizens', 'notVerifiedCitizens', 'notVerifiedCount', 'encryptedBarangayId')
            );
        } catch (\Exception $e) {
            abort(404, 'Invalid Barangay ID');
        }
    }

    /**
     * Verify a beneficiary.
     */
    public function verifyBeneficiary($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);
        $beneficiary->verified = true;
        $beneficiary->save();

        // Send verification email
        try {
            Mail::to($beneficiary->email)->send(new BeneficiaryVerified($beneficiary));
        } catch (\Exception $e) {
            return back()->with('error', 'Beneficiary verified but email could not be sent.');
        }

        return back()->with('success', 'Beneficiary verified successfully and notification sent.');
    }

    /**
     * Display the index page.
     */

    public function selectBarangay()
    {
        $barangays = Barangay::withCount([
            'beneficiaries as verified_count' => function ($query) {
                $query->where('verified', true);
            },
            'beneficiaries as not_verified_count' => function ($query) {
                $query->where('verified', false);
            }
        ])->get();

        return view('content.admin-interface.beneficiaries.senior-citizen.select-barangay', compact('barangays'));
    }

    public function verifiedBeneficiaries($encryptedBarangayId, Request $request)
    {
        $barangayId = Crypt::decrypt($encryptedBarangayId);
        $barangay = Barangay::findOrFail($barangayId);

        $query = Beneficiary::where('barangay_id', $barangayId)
            ->where('verified', true);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('osca_number', 'like', "%{$search}%");
            });
        }

        $verifiedBeneficiaries = $query->get();

        return view(
            'content.admin-interface.beneficiaries.senior-citizen.view-registered-senior-citizen.verified-beneficiaries',
            compact('barangay', 'verifiedBeneficiaries', 'encryptedBarangayId')
        );
    }

    public function notVerifiedBeneficiaries($encryptedBarangayId, Request $request)
    {
        $barangayId = Crypt::decrypt($encryptedBarangayId);
        $barangay = Barangay::findOrFail($barangayId);

        $query = Beneficiary::where('barangay_id', $barangayId)
            ->where('verified', false);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('osca_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $notVerifiedBeneficiaries = $query->get();

        return view(
            'content.admin-interface.beneficiaries.senior-citizen.view-registered-senior-citizen.not-verified-beneficiaries',
            compact('barangay', 'notVerifiedBeneficiaries', 'encryptedBarangayId')
        );
    }

    public function disableBeneficiary($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);
        $beneficiary->verified = false;
        $beneficiary->save();

        // Send disabled notification email
        try {
            Mail::to($beneficiary->email)->send(new BeneficiaryDisabled($beneficiary));
        } catch (\Exception $e) {
            return back()->with('error', 'Beneficiary disabled but email could not be sent.');
        }

        return back()->with('success', 'Beneficiary verification disabled and notification sent.');
    }

    public function editBeneficiary(Request $request, $id)
    {
        $beneficiary = Beneficiary::findOrFail($id);
        $beneficiary->update($request->all());

        return back()->with('success', 'Beneficiary updated successfully.');
    }

    public function deleteBeneficiary($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);
        $beneficiary->delete();

        return back()->with('success', 'Beneficiary deleted successfully.');
    }

}
