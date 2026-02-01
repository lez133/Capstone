{{-- filepath: c:\Lara\Capstone-MIS\resources\views\content\brgyrepresentative-interface\notification\view-notification.blade.php --}}

@extends('layouts.brgylayout')
@section('title', 'My Notifications')

@section('content')
<div class="container py-4">
  <h3 class="mb-3">Notifications</h3>

  @if($notifications->isEmpty())
    <div class="text-muted">No notifications found.</div>
  @else
    <div class="list-group mb-4">
      @foreach($notifications as $n)
        @if($n->user_id === Auth::id() && $n->sender_id !== Auth::id())
          @php
            $sender = $n->sender_id ? \App\Models\MSWDMember::find($n->sender_id) : null;
            $senderName = $sender ? trim(($sender->fname ?? '') . ' ' . ($sender->lname ?? '')) : 'System';
            $senderRole = $sender && $sender->role ? ucfirst(str_replace('_', ' ', $sender->role)) : '';
          @endphp
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <strong>
                You received a notification from
                {{ $senderName }}{{ $senderRole ? " ($senderRole)" : "" }}
              </strong>
              <br>
              <span class="text-muted">Subject: {{ $n->subject ?? '-' }}</span>
              <br>
              <span class="text-muted">Received: {{ optional($n->created_at)->format('Y-m-d H:i') ?? '-' }}</span>
            </div>
            @if($n->type === 'email')
              <a href="https://mail.google.com/mail/u/0/#inbox" target="_blank" class="btn btn-sm btn-outline-success">
                Open Gmail
              </a>
            @endif
          </div>
        @endif
      @endforeach
    </div>
    <div class="p-3">
      {{ $notifications->links() }}
    </div>
  @endif
</div>
@endsection
