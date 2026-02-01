@extends('layouts.beneficiarieslayout')

@section('content')
@php
    $user = Auth::guard('beneficiary')->user();
@endphp

<div class="container py-4">
    <h3>Notifications</h3>
    @if($notifications->isEmpty())
        <div class="alert alert-info">You have no notifications.</div>
    @else
        <div class="list-group">
            @foreach($notifications as $notification)
                @php
                    $created = \Carbon\Carbon::parse($notification['created_at']);
                    $isNew = $created->diffInHours(now()) < 24;
                @endphp
                <div class="list-group-item shadow-sm mb-3 rounded border border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-primary fs-5">{{ $notification['aid_program'] }}</div>
                            <div class="mb-1 text-secondary">
                                <i class="bi bi-calendar-event"></i>
                                {{ $notification['message'] }}
                            </div>
                        </div>
                        @if($isNew)
                            <span class="badge bg-success">New</span>
                        @endif
                    </div>
                    <div class="small text-muted mt-1">
                        {{ $created->diffForHumans() }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
