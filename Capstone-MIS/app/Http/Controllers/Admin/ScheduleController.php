<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\AidProgram;
use App\Models\Barangay;
use Illuminate\Http\Request;
use App\Models\Beneficiary;
use App\Models\Requirement;
use Illuminate\Support\Facades\Mail;
use App\Mail\EligibilityNotificationMail;
use App\Mail\UnpublishNotificationMail;

class ScheduleController extends Controller
{
    /**
     * Display the schedule page.
     */
    public function index()
    {
        $schedules = Schedule::with('aidProgram')->get();
        $aidPrograms = AidProgram::orderBy('aid_program_name')->get();

        // Add status to each schedule
        $now = now();
        foreach ($schedules as $schedule) {
            if ($now->lt($schedule->start_date)) {
                $schedule->status = 'Upcoming';
            } elseif ($now->between($schedule->start_date, $schedule->end_date)) {
                $schedule->status = 'Ongoing';
            } else {
                $schedule->status = 'Completed';
            }
        }

        return view('content.admin-interface.programs.schedule.schedule', compact('schedules', 'aidPrograms'));
    }

    public function create()
    {
        $aidPrograms = AidProgram::orderBy('aid_program_name')->get();
        $barangays = Barangay::orderBy('barangay_name')->get();
        return view('content.admin-interface.programs.schedule.create-schedule', compact('aidPrograms', 'barangays'));
    }

    public function store(Request $request)
    {
        $rules = [
            'aid_program_id' => 'required|exists:aid_programs,id',
            'beneficiary_type' => 'required|in:senior,pwd,both',
            'start_date' => 'required|date|before:end_date',
            'end_date' => 'required|date|after:start_date',
        ];

        // Only require barangay_ids for senior or both
        if (in_array($request->beneficiary_type, ['senior', 'both'])) {
            $rules['barangay_ids'] = 'required|array|min:1';
        }

        $validated = $request->validate($rules);

        Schedule::create([
            'aid_program_id' => $request->aid_program_id,
            'barangay_ids' => $request->barangay_ids ?? [],
            'beneficiary_type' => $request->beneficiary_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('schedules.index')->with('success', 'Schedule created successfully!');
    }

    public function publish($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->published = true;
        $schedule->save();
        return redirect()->route('schedules.index')->with('success', 'Schedule published!');
    }

    public function publishNotify($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->published = true;
        $schedule->save();

        // Get requirements for the aid program
        $requirements = $schedule->aidProgram->requirements->pluck('document_requirement')->toArray();

        // Get verified beneficiaries in selected barangays
        $barangayIds = $schedule->barangay_ids ?? [];
        $beneficiaries = Beneficiary::where('verified', true)
            ->whereIn('barangay_id', $barangayIds)
            ->get();

        foreach ($beneficiaries as $beneficiary) {
            Mail::to($beneficiary->email)->send(new EligibilityNotificationMail(
                $beneficiary,
                $schedule->aidProgram,
                $requirements,
                $schedule
            ));
        }

        return redirect()->route('schedules.index')->with('success', 'Schedule published and notifications sent!');
    }

    public function unpublish($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->published = false;
        $schedule->save();
        return redirect()->route('schedules.index')->with('success', 'Schedule unpublished!');
    }

    public function unpublishNotify($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->published = false;
        $schedule->save();

        $barangayIds = $schedule->barangay_ids ?? [];
        $beneficiaries = Beneficiary::where('verified', true)
            ->whereIn('barangay_id', $barangayIds)
            ->get();

        foreach ($beneficiaries as $beneficiary) {
            Mail::to($beneficiary->email)->send(new UnpublishNotificationMail(
                $beneficiary,
                $schedule->aidProgram,
                $schedule
            ));
        }

        return redirect()->route('schedules.index')->with('success', 'Schedule unpublished and notifications sent!');
    }

    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        $aidPrograms = AidProgram::orderBy('aid_program_name')->get();
        $barangays = Barangay::orderBy('barangay_name')->get();
        return view('content.admin-interface.programs.schedule.edit-schedule', compact('schedule', 'aidPrograms', 'barangays'));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'aid_program_id' => 'required|exists:aid_programs,id',
            'beneficiary_type' => 'required|in:senior,pwd,both',
            'start_date' => 'required|date|before:end_date',
            'end_date' => 'required|date|after:start_date',
        ];

        if (in_array($request->beneficiary_type, ['senior', 'both'])) {
            $rules['barangay_ids'] = 'required|array|min:1';
        }

        $validated = $request->validate($rules);

        $schedule = Schedule::findOrFail($id);
        $schedule->update([
            'aid_program_id' => $request->aid_program_id,
            'barangay_ids' => $request->barangay_ids ?? [],
            'beneficiary_type' => $request->beneficiary_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('schedules.index')->with('success', 'Schedule updated successfully!');
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();
        return redirect()->route('schedules.index')->with('success', 'Schedule deleted successfully!');
    }
}
