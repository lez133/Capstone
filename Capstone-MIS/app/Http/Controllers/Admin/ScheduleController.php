<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\AidProgram;
use App\Models\Barangay;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display the schedule page.
     */
    public function index()
    {
        $schedules = Schedule::with('aidProgram')->orderBy('start_date', 'asc')->get();
        return view('content.admin-interface.programs.schedule.schedule', compact('schedules'));
    }

    public function create()
    {
        $aidPrograms = AidProgram::orderBy('aid_program_name')->get();
        $barangays = Barangay::orderBy('barangay_name')->get();
        return view('content.admin-interface.programs.schedule.create-schedule', compact('aidPrograms', 'barangays'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'aid_program_id' => 'required|exists:aid_programs,id',
            'barangay_ids' => 'required|array|min:1', // Ensure it's an array with at least one item
            'beneficiary_type' => 'required|in:senior,pwd,both',
            'start_date' => 'required|date|before:end_date',
            'end_date' => 'required|date|after:start_date',
        ]);

        Schedule::create([
            'aid_program_id' => $request->aid_program_id,
            'barangay_ids' => $request->barangay_ids, // Store the array of barangay IDs
            'beneficiary_type' => $request->beneficiary_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('schedules.index')->with('success', 'Schedule created successfully!');
    }
}
