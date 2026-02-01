<?php

namespace App\Http\Controllers\BrgyRepresentative;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\MSWDMember;

class BrgyRepDashboardController extends Controller
{
    public function index()
    {
        $rep = auth()->guard('brgyrep')->user();
        $beneficiaries = [];
        $data = [
            'total_beneficiaries' => 0,
            'pwds_count' => 0,
            'senior_citizens_count' => 0,
        ];

        if ($rep && $rep->barangay_id) {
            $query = Beneficiary::where('barangay_id', $rep->barangay_id)
                ->where('verified', true);

            // Filter by type if provided
            $type = request('beneficiary_type');
            if ($type === 'pwd') {
                $query->where('beneficiary_type', 'PWD');
            } elseif ($type === 'senior') {
                $query->where('beneficiary_type', 'Senior Citizen');
            }

            $beneficiaries = $query->orderBy('last_name')->paginate(10);

            $data['total_beneficiaries'] = $beneficiaries->total();
            $data['pwds_count'] = Beneficiary::where('barangay_id', $rep->barangay_id)
                ->where('beneficiary_type', 'PWD')
                ->where('verified', true)
                ->count();
            $data['senior_citizens_count'] = Beneficiary::where('barangay_id', $rep->barangay_id)
                ->where('beneficiary_type', 'Senior Citizen')
                ->where('verified', true)
                ->count();
        }

        return view('content.brgyrepresentative-interface.dashboard.brgyrep-interface', compact('beneficiaries', 'data'));
    }
}
