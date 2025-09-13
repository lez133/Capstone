@extends('layouts.adminlayout')

@section('content')
<h1 class="mb-4">Send Notifications</h1>
<ul class="nav nav-tabs" id="notificationTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="sms-tab" data-bs-toggle="tab" data-bs-target="#sms" type="button" role="tab" aria-controls="sms" aria-selected="true">SMS</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="gmail-tab" data-bs-toggle="tab" data-bs-target="#gmail" type="button" role="tab" aria-controls="gmail" aria-selected="false">Gmail</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="notice-tab" data-bs-toggle="tab" data-bs-target="#notice" type="button" role="tab" aria-controls="notice" aria-selected="false">Beneficiary Notices</button>
    </li>
</ul>
<div class="tab-content mt-3" id="notificationTabsContent">
    <!-- SMS Tab -->
    <div class="tab-pane fade show active" id="sms" role="tabpanel" aria-labelledby="sms-tab">
        <form method="POST" action="{{ route('notifications.sms') }}">
            @csrf
            <div class="mb-3">
                <label for="smsRecipient" class="form-label">Recipient</label>
                <input type="text" class="form-control" id="smsRecipient" name="recipient" placeholder="Enter phone number" required>
            </div>
            <div class="mb-3">
                <label for="smsMessage" class="form-label">Message</label>
                <textarea class="form-control" id="smsMessage" name="message" rows="3" placeholder="Enter your message" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send SMS</button>
        </form>
    </div>
    <!-- Gmail Tab -->
    <div class="tab-pane fade" id="gmail" role="tabpanel" aria-labelledby="gmail-tab">
        <form method="POST" action="{{ route('notifications.gmail') }}">
            @csrf
            <div class="mb-3">
                <label for="gmailRecipient" class="form-label">Recipient</label>
                <select class="form-select" id="gmailRecipient" name="recipient" required>
                    <option value="all">All Registered Users</option>
                    @foreach($members as $member)
                        <option value="{{ $member->email }}">{{ $member->fname }} {{ $member->lname }} ({{ $member->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="gmailSubject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="gmailSubject" name="subject" placeholder="Enter subject" required>
            </div>
            <div class="mb-3">
                <label for="gmailMessage" class="form-label">Message</label>
                <textarea class="form-control" id="gmailMessage" name="message" rows="3" placeholder="Enter your message" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Gmail</button>
        </form>
    </div>
    <!-- Beneficiary Notices Tab -->
    <div class="tab-pane fade" id="notice" role="tabpanel" aria-labelledby="notice-tab">
        <form method="POST" action="{{ route('notifications.notice') }}">
            @csrf
            <div class="mb-3">
                <label for="noticeRecipients" class="form-label">Recipients</label>
                <p>Blank</p>
            </div>
            <div class="mb-3">
                <label for="noticeMessage" class="form-label">Message</label>
                <textarea class="form-control" id="noticeMessage" name="message" rows="3" placeholder="Enter your message" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Notice</button>
        </form>
    </div>
</div>
@endsection
