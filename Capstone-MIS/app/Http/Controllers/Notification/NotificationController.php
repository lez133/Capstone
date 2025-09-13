<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\AnnouncementMail;
use App\Models\MSWDMember;

class NotificationController extends Controller
{
    public function index()
    {
        $members = MSWDMember::all();
        return view('content.admin-interface.Notification.send-notification', compact('members'));
    }

    public function sendSms(Request $request)
    {
        // Validate input
        $request->validate([
            'recipient' => 'required|string',
            'message' => 'required|string',
        ]);

        // Placeholder logic for SMS sending
        return back()->with('success', 'SMS sent successfully!');
    }

    public function sendGmail(Request $request)
    {
        // Validate input
        $request->validate([
            'recipient' => 'required', // Can be 'all' or a specific email
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        // Check if the recipient is 'all'
        if ($request->recipient === 'all') {
            // Get all registered users' emails
            $recipients = MSWDMember::pluck('email')->toArray();
        } else {
            // Single recipient
            $recipients = [$request->recipient];
        }

        // Send email to each recipient
        foreach ($recipients as $recipient) {
            Mail::to($recipient)->send(new AnnouncementMail($request->subject, $request->message));
        }

        return back()->with('success', 'Email(s) sent successfully!');
    }

    public function sendNotice(Request $request)
    {
        // Validate input
        $request->validate([
            'recipients' => 'required|array',
            'message' => 'required|string',
        ]);

        if (in_array('all', $request->recipients)) {
            // Logic for sending to all beneficiaries
        } else {
            // Logic for sending to selected beneficiaries
        }

        return back()->with('success', 'Notice sent successfully!');
    }
}
