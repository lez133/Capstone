@extends('layouts.brgylayout')

@section('title', 'Barangay Representative Dashboard')

@section('content')
<div class="container py-4">
  <div class="row g-4">
    @if($data['assist_registration'])
    <div class="col-sm-6 col-lg-4">
      <a href="#" class="text-decoration-none">
        <div class="card dashboard-card p-4 text-center shadow-sm">
          <i class="fas fa-user-edit text-primary mb-3 fa-2x"></i>
          <h5 class="fw-bold">Assist Registration</h5>
          <p class="text-muted small">Help PWDs and senior citizens complete registration forms and upload documents.</p>
        </div>
      </a>
    </div>
    @endif

    @if($data['submit_aid_requests'])
    <div class="col-sm-6 col-lg-4">
      <a href="#" class="text-decoration-none">
        <div class="card dashboard-card p-4 text-center shadow-sm">
          <i class="fas fa-hands-helping text-success mb-3 fa-2x"></i>
          <h5 class="fw-bold">Submit Aid Requests</h5>
          <p class="text-muted small">Submit aid requests on behalf of beneficiaries and track their progress.</p>
        </div>
      </a>
    </div>
    @endif

    {{-- repeat the same pattern for manage_schedules, monitor_sms_notifications, track_applications --}}
  </div>
</div>
@endsection
