@extends('layouts.brgylayout')
@section('title', 'Notifications')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 fw-bold">Notifications</h2>
    <div class="row g-4">
        <!-- Send Notification Card -->
        <!-- View Notifications Card -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="fas fa-bell me-2"></i>View Notifications</h5>
                        <span class="badge bg-danger fs-6">
                            {{ $notificationCount ?? 0 }}
                        </span>
                    </div>
                    <p class="card-text text-muted mt-2">See all notifications you have received.</p>
                    <a href="{{ route('brgyrep.notifications.view') }}" class="btn btn-outline-primary mt-3 w-100">
                        <i class="fas fa-eye me-1"></i> View Notifications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
