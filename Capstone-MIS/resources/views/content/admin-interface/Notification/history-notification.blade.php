{{-- filepath: c:\Lara\Capstone-MIS\resources\views\content\admin-interface\Notification\history-notification.blade.php --}}

@extends('layouts.adminlayout')

@section('title', 'Notification History')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Notification History</h1>

    <ul class="nav nav-tabs mb-3" id="notifTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="send-tab" href="{{ route('notifications.index') }}" role="tab" aria-controls="tab-send" aria-selected="false">
                Send
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="history-tab" href="{{ route('notifications.history') }}" role="tab" aria-controls="tab-history" aria-selected="true">
                History
            </a>
        </li>
    </ul>

    @if($notifications->isEmpty())
        <div class="text-muted">No notifications found.</div>
    @else
        <div class="list-group mb-4">
            @foreach($notifications as $n)
                @php
                    $sender = $n->sender_id ? \App\Models\MSWDMember::find($n->sender_id) : null;
                    $recipient = $n->user_id ? \App\Models\MSWDMember::find($n->user_id) : null;
                    $senderName = $sender ? trim(($sender->fname ?? '') . ' ' . ($sender->lname ?? '')) : ($n->sender_name ?? 'Unknown sender');
                    $senderRole = $sender && $sender->role ? ucfirst(str_replace('_', ' ', $sender->role)) : '';
                    $recipientName = $recipient ? trim(($recipient->fname ?? '') . ' ' . ($recipient->lname ?? '')) : ($n->recipient ?? 'Unknown recipient');
                    $recipientRole = $recipient && $recipient->role ? ucfirst(str_replace('_', ' ', $recipient->role)) : '';
                    $isAdminSender = $n->sender_id === Auth::id();
                @endphp
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        @if($isAdminSender)
                            <strong>
                                You sent a notification to
                                {{ $recipientName }}{{ $recipientRole ? " ($recipientRole)" : "" }}
                            </strong>
                        @else
                            <strong>
                                You received a notification from
                                {{ $senderName }}{{ $senderRole ? " ($senderRole)" : "" }}
                            </strong>
                        @endif
                        <br>
                        <span class="text-muted">Subject: {{ $n->subject ?? '-' }}</span>
                        <br>
                        <span class="text-muted">Received: {{ optional($n->created_at)->format('Y-m-d H:i') ?? '-' }}</span>
                    </div>
                    @if(!$isAdminSender && $n->type === 'email')
                        <a href="https://mail.google.com/mail/u/0/#inbox" target="_blank" class="btn btn-sm btn-outline-success">
                            Open Gmail
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-2">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
