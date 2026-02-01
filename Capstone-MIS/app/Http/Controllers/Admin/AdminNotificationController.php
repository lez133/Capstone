<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\AnnouncementMail;
use App\Models\MSWDMember;
use App\Models\Notification;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Barangay;
use App\Models\Beneficiary;


class AdminNotificationController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index()
    {
        $members = MSWDMember::all();
        $barangays = Barangay::all();
        $beneficiaries = Beneficiary::all();
        return view('content.admin-interface.Notification.send-notification', compact('members', 'barangays', 'beneficiaries'));
    }

    public function sendSms(Request $request)
    {
        $request->validate([
            'recipient' => ['nullable', 'regex:/^(09\d{9}|639\d{9})$/'],
            'barangay_id' => 'nullable|integer|exists:barangays,id',
            'message' => 'required|string|max:918',
            'sender_id' => 'nullable|string|max:11',
        ]);

        $senderId = $request->filled('sender_id')
            ? $request->sender_id
            : config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', ''));

        if (empty($senderId)) {
            return back()->withInput()->with('error', 'No sender_id provided. Set a valid sender_id in the form or configure PHILSMS_DEFAULT_SENDER in .env.');
        }

        $recipients = [];

        // If barangay_id is selected, get all verified beneficiaries in that barangay
        if ($request->filled('barangay_id')) {
            $recipients = Beneficiary::where('barangay_id', $request->barangay_id)
                ->where('verified', true)
                ->pluck('phone') // <-- use 'phone' as the column name
                ->filter(function ($num) {
                    return preg_match('/^(09\d{9}|639\d{9})$/', $num);
                })
                ->map(function ($num) {
                    return preg_match('/^09\d{9}$/', $num) ? '63' . substr($num, 1) : $num;
                })
                ->unique()
                ->values()
                ->toArray();
        } elseif ($request->filled('recipient')) {
            $recipient = $request->recipient;
            $recipients[] = preg_match('/^09\d{9}$/', $recipient) ? '63' . substr($recipient, 1) : $recipient;
        }

        if (empty($recipients)) {
            return back()->withInput()->with('error', 'No valid recipient(s) found.');
        }

        try {
            $result = $this->smsService->sendBulkSms($recipients, $request->message, $senderId, null);

            if (($result['status'] ?? '') !== 'success') {
                $apiMsg = $result['message'] ?? ($result['raw'] ?? json_encode($result));
                return back()->withInput()->with('error', $apiMsg);
            }

            // Log notifications for each recipient
            foreach ($recipients as $recipient) {
                $recipientUser = \App\Models\MSWDMember::where('contact', $recipient)->first();
                if ($recipientUser) {
                    \App\Models\Notification::create([
                        'user_id'    => $recipientUser->id,
                        'sender_id'  => Auth::id(),
                        'sender_name'=> Auth::user()->fname . ' ' . Auth::user()->lname,
                        'recipient'  => $recipient,
                        'subject'    => null,
                        'message'    => $request->message,
                        'type'       => 'sms',
                        'status'     => 'sent',
                        'created_at' => now(),
                    ]);
                }
            }

            return back()->with('success', 'SMS sent successfully to ' . count($recipients) . ' recipient(s).');
        } catch (\Throwable $e) {
            Log::error('SMS send exception: '.$e->getMessage(), ['recipients' => $recipients, 'sender' => $senderId]);
            return back()->withInput()->with('error', 'Server error while sending SMS.');
        }
    }

    public function sendGmail(Request $request)
    {
        $request->validate([
            'recipient' => 'required',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        if ($request->recipient === 'all') {
            $recipients = \App\Models\MSWDMember::pluck('email')->filter()->toArray();
        } else {
            $recipients = [$request->recipient];
        }

        foreach ($recipients as $recipient) {
            Mail::to($recipient)->send(new \App\Mail\AnnouncementMail($request->subject, $request->message));

            $recipientUser = MSWDMember::where('email', $recipient)->first();

            if ($recipientUser) {
                Notification::create([
                    'user_id'    => $recipientUser->id, // Only the recipient will see this notification
                    'sender_id'  => Auth::id(),
                    'sender_name'=> Auth::user()->fname . ' ' . Auth::user()->lname,
                    'recipient'  => $recipient,
                    'subject'    => $request->subject ?? null,
                    'message'    => $request->message,
                    'type'       => 'email',
                    'status'     => 'sent',
                    'created_at' => now(),
                ]);
            }
        }

        return back()->with('success', 'Email(s) sent successfully!');
    }

    public function sendNotice(Request $request)
    {
        $request->validate([
            'recipients' => 'required|array',
            'message' => 'required|string',
        ]);

        // Implement your notice sending logic here

        return back()->with('success', 'Notice sent successfully!');
    }

    public function history()
    {
        $adminId = Auth::id();
        $notifications = Notification::where('sender_id', $adminId)
            ->orWhere('user_id', $adminId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('content.admin-interface.Notification.history-notification', compact('notifications'));
    }
}
