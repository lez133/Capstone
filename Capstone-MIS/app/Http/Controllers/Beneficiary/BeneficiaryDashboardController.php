<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BeneficiaryDocument;
use App\Models\Schedule;

class BeneficiaryDashboardController extends Controller
{
    public function index()
    {
        $beneficiary = auth('beneficiary')->user();

        if ($beneficiary) {
            // limit to 5 most recent submitted documents for the dashboard
            $documents = BeneficiaryDocument::where('beneficiary_id', $beneficiary->id)
                ->orderByDesc('created_at') // or 'uploaded_at' if your model uses that
                ->take(5)
                ->get();
        } else {
            $documents = collect();
        }

        // Get the 10 most recent schedules (applications) for this beneficiary
        $applications = Schedule::whereJsonContains('barangay_ids', $beneficiary ? $beneficiary->barangay_id : null)
            ->where(function ($q) use ($beneficiary) {
                $q->whereNull('beneficiary_type')
                  ->orWhere('beneficiary_type', $beneficiary->beneficiary_type);
            })
            ->latest()
            ->take(10)
            ->get();

        return view('content.beneficiary-interface.dashboard.beneficiaries-dashboard', compact('beneficiary', 'documents', 'applications'));
    }
}
