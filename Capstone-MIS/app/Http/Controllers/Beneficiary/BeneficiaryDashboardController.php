<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BeneficiaryDashboardController extends Controller
{
    public function index()
    {
        return view('content.beneficiary-interface.dashboard.beneficiaries-dashboard');
    }
}
