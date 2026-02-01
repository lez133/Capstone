<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;
use App\Notifications\DistributionReceivedNotification;

use App\Models\Schedule;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\AidProgramRequirementBeneficiary;
use App\Models\AidReceipt;

class DistributionController extends Controller
{
    /**
     * Step 1 – Category Page
     */
    public function category(Request $request)
    {
        $selected = $request->query('barangay_id');

        // Decrypt if needed (ensure we urldecode first if the value was URL-encoded)
        if ($selected && !is_numeric($selected)) {
            try {
                $selected = urldecode($selected);
                $selected = decrypt($selected);
            } catch (\Throwable $e) {}
        }

        // Load barangay (from query or session)
        if ($selected) {
            $barangay = Barangay::find($selected);
            if ($barangay) {
                Session::put('distribution.selected_barangay', $barangay->id);
            }
        } else {
            $sid = Session::get('distribution.selected_barangay');
            $barangay = $sid ? Barangay::find($sid) : null;
        }

        return view('content.admin-interface.distribution.category', compact('barangay'));
    }


    /**
     * Barangay Selector
     */
    public function BrgySelection()
    {
        return $this->selectBarangay();
    }

    public function selectBarangay()
    {
        $today = Carbon::now()->toDateString();

        $barangays = Barangay::orderBy('barangay_name')->get()->map(function ($barangay) use ($today) {
            $brgyId = $barangay->id;

            // Helper to build schedule query that matches this barangay (same logic as schedules())
            $buildQueryForBarangay = function () use ($brgyId) {
                return Schedule::where(function ($q) use ($brgyId) {
                    $q->whereJsonContains('barangay_ids', $brgyId)
                      ->orWhereRaw('FIND_IN_SET(?, barangay_ids)', [$brgyId])
                      ->orWhere('barangay_ids', $brgyId)
                      ->orWhere('barangay_ids', 'like', '%"'.$brgyId.'"%')
                      ->orWhere('barangay_ids', 'like', '%,'.$brgyId.',%')
                      ->orWhere('barangay_ids', 'like', $brgyId.',%')
                      ->orWhere('barangay_ids', 'like', '%,'.$brgyId);
                });
            };

            // counts
            $upcoming = (clone $buildQueryForBarangay())->whereDate('start_date', '>', $today)->count();
            $ongoing  = (clone $buildQueryForBarangay())->whereDate('start_date', '<=', $today)
                                                      ->whereDate('end_date', '>=', $today)
                                                      ->count();
            $completed= (clone $buildQueryForBarangay())->whereDate('end_date', '<', $today)->count();

            // total schedules for this barangay
            $total    = (clone $buildQueryForBarangay())->count();

            return (object) array_merge($barangay->toArray(), [
                'distribution_counts' => [
                    'upcoming'  => $upcoming,
                    'ongoing'   => $ongoing,
                    'completed' => $completed,
                    'total'     => $total,
                ],
            ]);
        });

        return view('content.admin-interface.distribution.distribution-barangay-selector', compact('barangays'));
    }


    /**
     * Step 2 – Show Schedules Based on Status
     */
    public function schedules(Request $request, $status)
    {
        $today = now()->toDateString();
        $sort   = $request->query('sort', 'recent');
        $view   = $request->query('view', 'card');
        $search = $request->query('search', '');

        $status = strtolower($status);

        // Resolve incoming barangay id (urldecode -> decrypt -> numeric) or fall back to session
        $rawBarangay = $request->query('barangay_id');
        $resolvedBarangay = null;
        if ($rawBarangay) {
            if (!is_numeric($rawBarangay)) {
                try {
                    $rawBarangay = urldecode($rawBarangay);
                    $decrypted = decrypt($rawBarangay);
                    if (is_numeric($decrypted)) {
                        $resolvedBarangay = (int) $decrypted;
                    }
                } catch (\Throwable $e) {
                    // ignore - keep null
                }
            } else {
                $resolvedBarangay = (int) $rawBarangay;
            }

            if ($resolvedBarangay) {
                Session::put('distribution.selected_barangay', $resolvedBarangay);
            }
        } else {
            $sid = Session::get('distribution.selected_barangay');
            if ($sid && is_numeric($sid)) {
                $resolvedBarangay = (int) $sid;
            }
        }

        // --- DEBUG: temporary dd() to inspect resolved values and simple DB counts ---
        $matchCountJson = $resolvedBarangay ? Schedule::whereJsonContains('barangay_ids', $resolvedBarangay)->count() : null;
        $matchCountFind = $resolvedBarangay ? DB::table('schedules')->whereRaw('FIND_IN_SET(?, barangay_ids)', [$resolvedBarangay])->count() : null;


        // Build query
        $query = Schedule::with('aidProgram');

        /** Status Filtering */
        if ($status === 'upcoming') {
            $query->whereDate('start_date', '>', $today);
        } elseif ($status === 'ongoing') {
            $query->whereDate('start_date', '<=', $today)
                  ->whereDate('end_date', '>=', $today);
        } else {
            $query->whereDate('end_date', '<', $today);
        }

        /** Barangay Filter: support JSON array, CSV string, plain int, and JSON-text stored as string */
        if ($resolvedBarangay) {
            $brgyId = (int) $resolvedBarangay;
            $query->where(function ($q) use ($brgyId) {
                $q->whereJsonContains('barangay_ids', $brgyId)
                  ->orWhereRaw('FIND_IN_SET(?, barangay_ids)', [$brgyId])
                  ->orWhere('barangay_ids', $brgyId)
                  ->orWhere('barangay_ids', 'like', '%"'.$brgyId.'"%')   // JSON stored as text
                  ->orWhere('barangay_ids', 'like', '%,'.$brgyId.',%')
                  ->orWhere('barangay_ids', 'like', $brgyId.',%')
                  ->orWhere('barangay_ids', 'like', '%,'.$brgyId);
            });
        }

        /** Search Filter */
        if ($search) {
            $query->whereHas('aidProgram', function ($q) use ($search) {
                $q->where('aid_program_name', 'like', "%$search%");
            });
        }

        /** Sorting */
        if ($sort === 'recent')
            $query->orderBy('created_at', 'desc');
        elseif ($sort === 'early')
            $query->orderBy('start_date', 'asc');
        elseif ($sort === 'last')
            $query->orderBy('start_date', 'desc');

        // Quick debug snapshot (remove after confirming behavior)
        $totalSchedules = Schedule::count();
        $matchedCount = (clone $query)->count();

        $schedules = $query->get();

        /** Encrypt barangay for URLs */
        if ($resolvedBarangay) {
            $encryptedBarangay = urlencode(encrypt($resolvedBarangay));
        } else {
            $sid = Session::get('distribution.selected_barangay');
            $encryptedBarangay = $sid ? urlencode(encrypt($sid)) : null;
        }

        // pass debug for temporary inspection
        $debug = [
            'resolvedBarangay' => $resolvedBarangay,
            'totalSchedules'   => $totalSchedules,
            'matchedCount'     => $matchedCount,
            'status'           => $status,
        ];

        return view(
            'content.admin-interface.distribution.schedules',
            compact('schedules', 'status', 'sort', 'view', 'search', 'resolvedBarangay', 'encryptedBarangay', 'debug')
        );
    }


    public function beneficiaries(Request $request, $scheduleParam, $barangayParam)
    {
        // Decrypt schedule
        if (!is_numeric($scheduleParam)) {
            try {
                $scheduleParam = urldecode($scheduleParam);
                $scheduleParam = decrypt($scheduleParam);
            } catch (\Throwable $e) {}
        }

        // Decrypt barangay
        if (!is_numeric($barangayParam)) {
            try {
                $barangayParam = urldecode($barangayParam);
                $barangayParam = decrypt($barangayParam);
            } catch (\Throwable $e) {}
        }

        $schedule  = Schedule::with('aidProgram')->findOrFail($scheduleParam);
        $barangay  = Barangay::findOrFail($barangayParam);

        /** Beneficiaries filter */
        $query = Beneficiary::where('verified', true)
                            ->where('barangay_id', $barangayParam);

        $type = $schedule->beneficiary_type;

        if ($type && $type !== 'both') {
            if ($type === 'senior') {
                $query->whereIn('beneficiary_type', ['senior', 'Senior Citizen']);
            } elseif ($type === 'pwd') {
                $query->whereIn('beneficiary_type', ['pwd', 'PWD']);
            } else {
                $query->where('beneficiary_type', $type);
            }
        }

        $beneficiaries = $query->get();
        $requirements  = $schedule->aidProgram->requirements()->get();

        /** Requirement Status Mapping */
        foreach ($beneficiaries as $b) {
            $statusList = [];

            foreach ($requirements as $req) {
                $apr = AidProgramRequirementBeneficiary::where([
                    ['beneficiary_id', $b->id],
                    ['barangay_id', $barangayParam],
                    ['aid_program_id', $schedule->aid_program_id],
                    ['requirement_id', $req->id],
                ])->first();

                $statusList[] = [
                    'name'        => $req->document_requirement,
                    'status'      => $apr->status ?? 'Not Submitted',
                    'document'    => $apr->beneficiaryDocument->file_path ?? null,
                    'document_id' => $apr->beneficiaryDocument->id ?? null,
                ];
            }

            $b->is_eligible = collect($statusList)->where('status', 'Validated')->count() === count($requirements);
            $b->requirements_status = $statusList;
        }

        $encryptedSchedule = urlencode(encrypt($scheduleParam));
        $encryptedBarangay = urlencode(encrypt($barangayParam));

        return view(
            'content.admin-interface.distribution.beneficiaries',
            compact('schedule', 'barangay', 'beneficiaries', 'encryptedSchedule', 'encryptedBarangay')
        );
    }


    /**
     * Mark beneficiary as received
     */
    public function markReceived(Request $request)
    {
        $request->validate([
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'schedule_id'    => 'required|exists:schedules,id',
        ]);

        $exists = DB::table('program_beneficiary_receipts')
            ->where('beneficiary_id', $request->beneficiary_id)
            ->where('schedule_id', $request->schedule_id)
            ->exists();

        if (!$exists) {
            DB::table('program_beneficiary_receipts')->insert([
                'beneficiary_id' => $request->beneficiary_id,
                'schedule_id'    => $request->schedule_id,
                'received_at'    => now(),
            ]);
        }

        // Notify beneficiary
        $beneficiary = \App\Models\Beneficiary::find($request->beneficiary_id);
        $schedule = \App\Models\Schedule::with('aidProgram')->find($request->schedule_id);

        if ($beneficiary && $schedule && $schedule->aidProgram) {
            // --- SMS Notification (with validation and logging) ---
            $smsDebug = null;
            try {
                $phone = $beneficiary->mobile_number ?? $beneficiary->contact_number ?? $beneficiary->phone ?? $beneficiary->cellphone ?? $beneficiary->contact_no ?? null;

                if ($phone) {
                    $digits = preg_replace('/\D+/', '', (string) $phone);

                    if (preg_match('/^09\d{9}$/', $digits)) {
                        $recipient = '63' . substr($digits, 1);
                    } elseif (preg_match('/^63\d{10}$/', $digits)) {
                        $recipient = $digits;
                    } else {
                        Log::warning('Invalid phone for beneficiary', [
                            'beneficiary_id' => $beneficiary->id,
                            'phone' => $digits,
                        ]);
                        $smsDebug = 'Invalid phone format: ' . $digits;
                        $recipient = null;
                    }

                    if ($recipient) {
                        $message = "Your aid program '{$schedule->aidProgram->aid_program_name}' is ready for claiming. Please claim your aid at your designated barangay.";
                        $sender = config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', 'PhilSMS'));

                        if (empty($sender)) {
                            $smsDebug = 'No PHILSMS sender configured (PHILSMS_DEFAULT_SENDER).';
                            Log::error('No PHILSMS sender configured.');
                        } else {
                            $smsService = new \App\Services\SmsService();
                            $result = $smsService->sendBulkSms([$recipient], $message, $sender, null);

                            $status = strtolower((string)($result['status'] ?? 'error'));
                            $apiMessage = $result['message'] ?? null;
                            if (empty($apiMessage) && isset($result['raw']) && is_array($result['raw'])) {
                                $apiMessage = $result['raw']['message'] ?? json_encode(array_slice($result['raw'], 0, 3));
                            }

                            Log::info('PhilSMS distribution', [
                                'beneficiary_id' => $beneficiary->id,
                                'recipient'      => $recipient,
                                'sender'         => $sender,
                                'status'         => $status,
                                'api_message'    => is_string($apiMessage) ? mb_substr($apiMessage, 0, 200) : null,
                            ]);

                            if ($status !== 'success') {
                                $smsDebug = 'PhilSMS error: ' . (is_string($apiMessage) ? $apiMessage : 'Unknown error');
                            } else {
                                $smsDebug = 'PhilSMS success: message accepted';
                            }
                        }
                    }
                } else {
                    $smsDebug = 'No phone number available for beneficiary.';
                }
            } catch (\Throwable $e) {
                Log::error('SMS send exception for distribution', [
                    'beneficiary_id' => $beneficiary->id,
                    'error' => $e->getMessage(),
                ]);
                $smsDebug = 'SMS exception: ' . $e->getMessage();
            }

            // --- Email Notification (with logging, no UI error) ---
            try {
                if (!empty($beneficiary->email)) {
                    Mail::raw(
                        "Your aid program '{$schedule->aidProgram->aid_program_name}' is ready for claiming. Please claim your aid at your designated barangay.",
                        function ($message) use ($beneficiary) {
                            $message->to($beneficiary->email)
                                    ->subject('Aid Program Distribution Notification');
                        }
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('Distribution email failed', [
                    'beneficiary_id' => $beneficiary->id,
                    'error' => $e->getMessage(),
                ]);
                // Do not flash email errors to UI
            }

            // Optionally, flash SMS debug info for admin
            session()->flash('sms_debug', $smsDebug);
        }

        return back()->with('success', 'Distribution confirmed and beneficiary notified.');
    }


    public function exportBeneficiariesCsv($scheduleId, $barangayId)
    {
        // Decrypt both if needed
        if (!is_numeric($scheduleId)) {
            try { $scheduleId = decrypt($scheduleId); } catch (\Throwable $e) {}
        }

        if (!is_numeric($barangayId)) {
            try { $barangayId = decrypt($barangayId); } catch (\Throwable $e) {}
        }

        $schedule  = Schedule::findOrFail($scheduleId);
        $barangay  = Barangay::findOrFail($barangayId);

        $query = Beneficiary::where('verified', true)
                        ->where('barangay_id', $barangayId);

        $type = $schedule->beneficiary_type;

        if ($type && $type !== 'both') {
            if ($type === 'senior')
                $query->whereIn('beneficiary_type', ['senior', 'Senior Citizen']);
            elseif ($type === 'pwd')
                $query->whereIn('beneficiary_type', ['pwd', 'PWD']);
            else
                $query->where('beneficiary_type', $type);
        }

        $beneficiaries = $query->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="beneficiaries.csv"',
        ];

        $columns = [
            'last_name', 'first_name', 'middle_name',
            'beneficiary_type', 'status', 'barangay_id', 'is_eligible',
            'distribution_confirmed', 'beneficiary_receipt', 'receipt_date'
        ];

        $callback = function () use ($beneficiaries, $columns, $schedule) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Last Name', 'First Name', 'Middle Name',
                'Type', 'Status', 'Barangay ID', 'Is Eligible',
                'Distribution Confirmed', 'Beneficiary Receipt', 'Receipt Date'
            ]);

            foreach ($beneficiaries as $b) {
                // Distribution Confirmed
                $alreadyReceived = DB::table('program_beneficiary_receipts')
                    ->where('beneficiary_id', $b->id)
                    ->where('schedule_id', $schedule->id)
                    ->exists();

                // Beneficiary Receipt
                $aidReceipt = \App\Models\AidReceipt::where('beneficiary_id', $b->id)
                    ->where('schedule_id', $schedule->id)
                    ->first();

                // If not found in aid_receipts but found in program_beneficiary_receipts, treat as confirmed
                $receiptDate = null;
                $beneficiaryReceipt = 'Not Yet Confirmed';
                if (!$aidReceipt && $alreadyReceived) {
                    $receiptDate = DB::table('program_beneficiary_receipts')
                        ->where('beneficiary_id', $b->id)
                        ->where('schedule_id', $schedule->id)
                        ->value('received_at');
                    $beneficiaryReceipt = 'Confirmed by Beneficiary';
                } elseif ($aidReceipt && $aidReceipt->receipt_date) {
                    $receiptDate = $aidReceipt->receipt_date;
                    $beneficiaryReceipt = 'Confirmed by Beneficiary';
                }

                fputcsv($file, [
                    $b->last_name,
                    $b->first_name,
                    $b->middle_name,
                    $b->beneficiary_type,
                    'Verified',
                    $b->barangay_id,
                    $b->is_eligible ? 'Yes' : 'No',
                    $alreadyReceived ? 'Yes' : 'No',
                    $beneficiaryReceipt,
                    $receiptDate ? \Carbon\Carbon::parse($receiptDate)->format('m/d/Y h:i A') : '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
