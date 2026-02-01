<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Schedule;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index()
    {
        $beneficiary = Auth::guard('beneficiary')->user();
        if (!$beneficiary) {
            Log::warning('Unauthorized access to notifications');
            abort(403, 'Unauthorized');
        }

        $barangayId = (int) ($beneficiary->barangay_id ?? 0);
        if ($barangayId <= 0) {
            Log::warning('Beneficiary missing barangay_id', ['beneficiary_id' => $beneficiary->id]);
            abort(500, 'Configuration error');
        }

        $isSenior = !empty($beneficiary->osca_number);
        $isPwd = !empty($beneficiary->pwd_id);

        if (!$isSenior && !$isPwd) {
            Log::warning('Beneficiary not qualified for notifications', [
                'beneficiary_id' => $beneficiary->id,
                'barangay_id' => $barangayId
            ]);
            return view('content.beneficiary-interface.notificaton.beneficiary-notification', [
                'notifications' => collect(),
                'message' => 'You are not yet qualified for aid program notifications. Please complete your registration as a Senior Citizen or PWD.'
            ]);
        }

        $now = now();

        $schedules = Schedule::with('aidProgram')
            ->where('published', true)
            ->where(function ($q) use ($barangayId) {
                $q->whereJsonContains('barangay_ids', (string) $barangayId)
                  ->orWhereJsonContains('barangay_ids', $barangayId);
            })
            ->where(function ($q) use ($isSenior, $isPwd) {
                $q->whereNull('beneficiary_type')
                  ->orWhere('beneficiary_type', '')
                  ->orWhere('beneficiary_type', 'All')
                  ->orWhere('beneficiary_type', 'all');

                if ($isSenior) {
                    $q->orWhere('beneficiary_type', 'senior')
                      ->orWhere('beneficiary_type', 'Senior')
                      ->orWhere('beneficiary_type', 'Senior Citizen')
                      ->orWhere('beneficiary_type', 'senior citizen')
                      ->orWhereRaw('LOWER(beneficiary_type) = ?', ['senior']);
                }

                if ($isPwd) {
                    $q->orWhere('beneficiary_type', 'pwd')
                      ->orWhere('beneficiary_type', 'PWD')
                      ->orWhere('beneficiary_type', 'Person With Disability')
                      ->orWhere('beneficiary_type', 'person with disability')
                      ->orWhereRaw('LOWER(beneficiary_type) = ?', ['pwd']);
                }
            })
            ->orderBy('start_date', 'desc')
            ->get();

        Log::info('Notifications loaded', [
            'beneficiary_id' => $beneficiary->id,
            'barangay_id' => $barangayId,
            'is_senior' => $isSenior,
            'is_pwd' => $isPwd,
            'schedules_count' => $schedules->count()
        ]);

        $notifications = collect();

        foreach ($schedules as $schedule) {
            $programName = $schedule->aidProgram->aid_program_name ?? 'Unknown Program';
            $start = $schedule->start_date ? Carbon::parse($schedule->start_date)->format('F d, Y h:i A') : 'N/A';
            $end = $schedule->end_date ? Carbon::parse($schedule->end_date)->format('F d, Y h:i A') : 'N/A';

            $notifications->push([
                'title' => 'Aid Program Notification',
                'aid_program' => $programName,
                'message' => "Schedule: $start to $end",
                'created_at' => $schedule->created_at,
            ]);
        }

        return view('content.beneficiary-interface.notificaton.beneficiary-notification', [
            'notifications' => $notifications
        ]);
    }
}
