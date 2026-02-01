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
use App\Services\SmsService;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;


class ScheduleController extends Controller
{

    public function index(Request $request)
    {
        $sort = $request->query('sort', 'recent');

        if ($sort === 'recent') {
            $schedules = Schedule::with('aidProgram')->orderBy('created_at', 'desc')->get();
        } elseif ($sort === 'date_asc') {
            $schedules = Schedule::with('aidProgram')->orderBy('start_date', 'asc')->get();
        } elseif ($sort === 'date_desc') {
            $schedules = Schedule::with('aidProgram')->orderBy('start_date', 'desc')->get();
        } else {
            $schedules = Schedule::with('aidProgram')->get();
        }

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

        return view('content.admin-interface.programs.schedule.schedule', compact('schedules', 'aidPrograms', 'sort'));
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

        // Require barangay_ids for senior, pwd or both
        if (in_array($request->beneficiary_type, ['senior', 'pwd', 'both'])) {
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

        $actorId = optional(Auth::guard('mswd')->user())->id ?? Auth::id();
        ActivityLogger::log(
            $actorId,
            'publish_schedule',
            Schedule::class,
            $schedule->id,
            ['title' => $schedule->title ?? null]
        );

        return redirect()->back()->with('success','Published');
    }

    public function publishNotify($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->published = true;
        $schedule->save();

        $requirements = $schedule->aidProgram->requirements->pluck('document_requirement')->toArray();
        $requirementsText = count($requirements) ? implode(', ', $requirements) : 'None';
        $barangayIds = $schedule->barangay_ids ?? [];
        $beneficiaries = Beneficiary::where('verified', true)
            ->whereIn('barangay_id', $barangayIds)
            ->get();

        // send email (only if beneficiary has an email) and don't block SMS on mail failure
        foreach ($beneficiaries as $beneficiary) {
            if (!empty($beneficiary->email)) {
                try {
                    Mail::to($beneficiary->email)->send(new EligibilityNotificationMail(
                        $beneficiary,
                        $schedule->aidProgram,
                        $requirements,
                        $schedule
                    ));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Eligibility email failed', [
                        'beneficiary_id' => $beneficiary->id,
                        'email' => $beneficiary->email,
                        'error' => $e->getMessage(),
                    ]);
                    // continue to SMS regardless of email result
                }
            } else {
                \Illuminate\Support\Facades\Log::info('Skipping email - no email address', [
                    'beneficiary_id' => $beneficiary->id
                ]);
            }
        }

        // SMS: send personalized SMS per beneficiary (include name, schedule period, aid program, requirements)
        $smsService = new SmsService();
        $sender = config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', 'PhilSMS'));

        $successCount = 0;
        $failCount = 0;
        foreach ($beneficiaries as $beneficiary) {
            // determine phone
            $raw = trim($beneficiary->phone ?? $beneficiary->mobile_number ?? $beneficiary->contact_number ?? '');
            $digits = preg_replace('/\D+/', '', $raw);

            // normalize to 63XXXXXXXXXX (accept 09... or 63...)
            $recipient = null;
            if (preg_match('/^09\d{9}$/', $digits)) {
                $recipient = '63' . substr($digits, 1);
            } elseif (preg_match('/^63\d{9,10}$/', $digits)) {
                $recipient = $digits;
            }

            if (empty($recipient)) {
                \Illuminate\Support\Facades\Log::warning('Skipping SMS - invalid phone', [
                    'beneficiary_id' => $beneficiary->id,
                    'phone' => $raw,
                ]);
                $failCount++;
                continue;
            }

            // build message
            $name = trim(($beneficiary->first_name ?? '') . ' ' . ($beneficiary->last_name ?? ''));
            $aidName = $schedule->aidProgram->aid_program_name ?? 'Aid Program';
            $start = \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y');
            $end = \Carbon\Carbon::parse($schedule->end_date)->format('M d, Y');
            $scheduleLabel = "{$start} - {$end}";

            $message = "Hi {$name}, you are eligible for \"{$aidName}\" scheduled {$scheduleLabel}. Requirements: {$requirementsText}. Please check your account for details.";

            try {
                $result = $smsService->sendBulkSms([$recipient], $message, $sender);
                $status = strtolower($result['status'] ?? 'error');
                if ($status === 'success') {
                    $successCount++;
                } else {
                    $failCount++;
                }


                \Illuminate\Support\Facades\Log::info('PhilSMS publishNotify', [
                    'beneficiary_id' => $beneficiary->id,
                    'recipient' => $recipient,
                    'sender' => $sender,
                    'status' => $status,
                    'api_message' => is_string($result['message'] ?? null) ? mb_substr($result['message'], 0, 200) : null,
                ]);
            } catch (\Throwable $e) {
                $failCount++;
                \Illuminate\Support\Facades\Log::error('PhilSMS exception publishNotify', [
                    'beneficiary_id' => $beneficiary->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $summary = "Schedule published. SMS notifications: {$successCount} sent, {$failCount} failed.";
        return redirect()->route('schedules.index')->with('success', 'Schedule published and notifications processed!')->with('sms_summary', $summary);
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

        // send email only when beneficiary has an email (log failures, continue)
        foreach ($beneficiaries as $beneficiary) {
            if (!empty($beneficiary->email)) {
                try {
                    Mail::to($beneficiary->email)->send(new UnpublishNotificationMail(
                        $beneficiary,
                        $schedule->aidProgram,
                        $schedule
                    ));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Unpublish email failed', [
                        'beneficiary_id' => $beneficiary->id,
                        'email' => $beneficiary->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::info('Skipping unpublish email - no email address', [
                    'beneficiary_id' => $beneficiary->id
                ]);
            }
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

    public function calendar()
    {
        $schedules = Schedule::with('aidProgram')->get();
        $barangayMap = Barangay::pluck('barangay_name', 'id')->toArray();

        $now = now();
        foreach ($schedules as $schedule) {
            if ($now->lt($schedule->start_date)) {
                $schedule->status = 'Upcoming';
            } elseif ($now->between($schedule->start_date, $schedule->end_date)) {
                $schedule->status = 'Ongoing';
            } else {
                $schedule->status = 'Completed';
            }

            // Map barangay_ids to barangay names and set as a new attribute
            $ids = is_array($schedule->barangay_ids) ? $schedule->barangay_ids : json_decode($schedule->barangay_ids, true);
            $barangayNames = [];
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if (isset($barangayMap[$id])) {
                        $barangayNames[] = $barangayMap[$id];
                    }
                }
            }
            $schedule->setAttribute('barangay_names', $barangayNames);
        }

        // Remove dd after debugging
        return view('content.admin-interface.programs.schedule.calendar-schedule', compact('schedules'));
    }


    public function show($id)
    {
        $schedule = Schedule::with('aidProgram')->findOrFail($id);
        // Add status for coloring
        $now = now();
        if ($now->lt($schedule->start_date)) {
            $schedule->status = 'Upcoming';
        } elseif ($now->between($schedule->start_date, $schedule->end_date)) {
            $schedule->status = 'Ongoing';
        } else {
            $schedule->status = 'Completed';
        }
        return view('content.admin-interface.programs.schedule.show-schedule', compact('schedule'));
    }
}
