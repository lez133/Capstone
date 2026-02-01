<?php

namespace App\Http\Controllers\BrgyRepresentative;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\MSWDMember;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;
use App\Mail\AnnouncementMail;
use Illuminate\Support\Facades\Log;
use Throwable;

class BrgyNotificationController extends Controller
{
    protected $smsService = null;

    public function __construct()
    {
        try {
            $this->smsService = app()->make(SmsService::class);
        } catch (Throwable $e) {
            $this->smsService = null;
            Log::debug('SmsService not available: ' . $e->getMessage());
        }
    }

    // show send form
    public function send()
    {
        $mswd_members = MSWDMember::orderBy('lname')->get();
        return view('content.brgyrepresentative-interface.notification.send-notification', compact('mswd_members'));
    }

    // send SMS only (same logic as AdminNotificationController)
    public function sendSms(Request $request)
    {
        $request->validate([
            'mswd_member_id' => 'required|exists:mswd_members,id',
            'message' => 'required|string|max:918',
        ]);

        $member = MSWDMember::findOrFail($request->mswd_member_id);
        $recipient = $member->contact;
        if (preg_match('/^09\d{9}$/', $recipient)) {
            $recipient = '63' . substr($recipient, 1);
        }

        // prefer configured sender or fail
        $senderId = config('services.philsms.default_sender', env('PHILSMS_DEFAULT_SENDER', ''));
        if (empty($senderId)) {
            return back()->with('error', 'No sender_id configured. Set PHILSMS_DEFAULT_SENDER in .env or configure a sender.');
        }

        if (! $this->smsService) {
            Log::warning('SmsService not bound when sending SMS', ['member_id' => $member->id]);
            return back()->with('error', 'SMS service not configured.');
        }

        try {
            Log::info('Attempting SMS send', ['member_id' => $member->id, 'contact' => $recipient, 'sender' => $senderId]);
            $result = $this->smsService->sendBulkSms([$recipient], $request->message, $senderId, null);

            if (($result['status'] ?? '') !== 'success') {
                $apiMsg = $result['message'] ?? ($result['raw'] ?? json_encode($result));
                if (stripos($apiMsg, 'not authorized') !== false || stripos($apiMsg, 'not authorised') !== false) {
                    $apiMsg .= ' — Sender ID is not authorized by PhilSMS. Use an authorized numeric sender (with country code) or a registered alphanumeric sender_id.';
                }
                Log::warning('PhilSMS send failed', ['recipient' => $recipient, 'sender' => $senderId, 'api' => $apiMsg]);
                return back()->with('error', $apiMsg);
            }

            return back()->with('success', 'SMS sent successfully.');
        } catch (Throwable $e) {
            Log::error('SMS send exception: '.$e->getMessage(), ['recipient' => $recipient, 'sender' => $senderId]);
            return back()->with('error', 'Server error while sending SMS.');
        }
    }

    // send Email only (Gmail via configured mailer) - same pattern as admin
    public function sendEmail(Request $request)
    {
        $request->validate([
            'recipient' => 'required',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        $recipients = $request->recipient === 'all'
            ? \App\Models\MSWDMember::pluck('email')->filter()->toArray()
            : [$request->recipient];

        foreach ($recipients as $recipient) {
            Mail::to($recipient)->send(new \App\Mail\AnnouncementMail($request->subject, $request->message));

            // Only look up in MSWDMember (since both roles are here)
            $recipientUser = MSWDMember::where('email', $recipient)->first();

            $sender = Auth::user();
            $senderName = null;
            if ($sender) {
                if (!empty($sender->name)) {
                    $senderName = $sender->name;
                } elseif (!empty($sender->full_name)) {
                    $senderName = $sender->full_name;
                } else {
                    $first = $sender->fname ?? $sender->first_name ?? '';
                    $last = $sender->lname ?? $sender->last_name ?? '';
                    $senderName = trim($first . ' ' . $last);
                    if ($senderName === '') {
                        $senderName = null;
                    }
                }
            }

            Notification::create([
                'user_id'    => $recipientUser ? $recipientUser->id : null, // recipient's user ID
                'sender_id'  => Auth::id(),
                'sender_name'=> $senderName,
                'recipient'  => $recipient,
                'subject'    => $request->subject ?? null,
                'message'    => $request->message,
                'type'       => 'email',
                'status'     => 'sent',
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Email(s) sent successfully!');
    }

    public function view()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->where('sender_id', '!=', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);
        return view('content.brgyrepresentative-interface.notification.view-notification', compact('notifications'));
    }

    public function interface()
    {
        $notificationCount = Notification::where('user_id', Auth::id())->count();
        return view('content.brgyrepresentative-interface.notification.notification-interface', compact('notificationCount'));
    }
}
