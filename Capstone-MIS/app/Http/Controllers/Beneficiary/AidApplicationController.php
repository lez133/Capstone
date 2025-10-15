<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedule;
use Carbon\Carbon;

class AidApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('beneficiary')->user();
        $now = now();

        $schedules = Schedule::with(['aidProgram.Requirements'])
            ->where('published', true)
            ->where('end_date', '>=', $now)
            ->get();

        $applications = [];
        foreach ($schedules as $schedule) {
            if ($now->lt($schedule->start_date)) {
                $schedule->status = 'Upcoming';
            } elseif ($now->between($schedule->start_date, $schedule->end_date)) {
                $schedule->status = 'Ongoing';
            } else {
                $schedule->status = 'Completed';
            }

            $requirements = [];
            if ($schedule->aidProgram && $schedule->aidProgram->requirements) {
                // Use the correct field name from your requirements table
                $requirements = $schedule->aidProgram->requirements->pluck('document_requirement')->toArray();
            }


            $applications[] = [
                'id' => 'APP-' . $schedule->id,
                'type' => $schedule->aidProgram->aid_program_name,
                'description' => $schedule->aidProgram->description ?? '',
                'amount' => $schedule->aidProgram->amount ?? 0,
                'status' => $schedule->status,
                'status_type' => $schedule->status === 'Ongoing' ? 'primary' : ($schedule->status === 'Upcoming' ? 'info' : 'secondary'),
                'progress' => 100,
                'applied' => Carbon::parse($schedule->start_date)->format('m/d/Y'),
                'updated' => Carbon::parse($schedule->updated_at)->format('m/d/Y'),
                'distribution_date' => Carbon::parse($schedule->end_date)->format('l, F d, Y'),
                'can_apply' => $schedule->status === 'Ongoing',
                'requirements' => $requirements,
            ];
        }

        return view('content.beneficiary-interface.aid-application.aid-application', compact('applications'));
    }
}
