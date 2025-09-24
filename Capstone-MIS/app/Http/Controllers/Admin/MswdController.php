<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeniorCitizenBeneficiary;
use App\Models\Barangay;
use App\Models\AidProgram;

class MswdController extends Controller
{
    public function index()
    {
        // Fetch total counts
        $totalBeneficiaries = SeniorCitizenBeneficiary::count();
        $totalBarangays = Barangay::count();
        $totalAidPrograms = AidProgram::count();

        // Fetch recent beneficiaries (limit to 5)
        $recentBeneficiaries = SeniorCitizenBeneficiary::with('barangay')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Fetch all aid programs
        $aidPrograms = AidProgram::orderBy('aid_program_name')->get();

        return view('content.admin-interface.dashboard.mswd-interface', compact(
            'totalBeneficiaries',
            'totalBarangays',
            'totalAidPrograms',
            'recentBeneficiaries',
            'aidPrograms'
        ));
    }
}
