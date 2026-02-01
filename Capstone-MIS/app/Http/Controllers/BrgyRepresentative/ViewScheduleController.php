<?php

namespace App\Http\Controllers\BrgyRepresentative;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Barangay;

class ViewScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with(['aidProgram', 'aidProgram.programType'])
            ->orderBy('start_date', 'desc')
            ->get();

        $barangays = Barangay::pluck('barangay_name', 'id')->all();

        return view('content.brgyrepresentative-interface.view-schedule.view-schedules', compact('schedules', 'barangays'));
    }
}
