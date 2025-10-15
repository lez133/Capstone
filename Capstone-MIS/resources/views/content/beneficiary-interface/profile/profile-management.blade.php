@extends('layouts.beneficiarieslayout')

@section('title', 'Profile Management')

@section('content')
<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            <div class="me-3">
              <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width:72px;height:72px;">
                <span class="text-white fs-4">{{ strtoupper(substr($beneficiary->first_name ?? $beneficiary->username,0,1)) }}</span>
              </div>
            </div>
            <div>
              <h5 class="mb-0">{{ $beneficiary->first_name ?? $beneficiary->username }}</h5>
              <small class="text-muted">{{ $beneficiary->email ?? 'N/A' }}</small>
            </div>
            <div class="ms-auto">
              @if($beneficiary->verified)
                <span class="badge bg-success">Verified</span>
              @else
                <span class="badge bg-secondary">Not verified</span>
              @endif
            </div>
          </div>
          <hr>
          <div class="row g-2">
            <div class="col-6">
              <div class="fw-semibold small text-uppercase">Barangay</div>
              <div class="text-muted">{{ $barangayName }}</div>
            </div>
            <div class="col-6">
              <div class="fw-semibold small text-uppercase">Beneficiary Type</div>
              <div class="text-muted">{{ $beneficiaryType }}</div>
            </div>
            <div class="col-12">
              <div class="fw-semibold small text-uppercase">{{ $oscaPwdLabel }}</div>
              <div class="text-muted">{{ $oscaPwdValue }}</div>
            </div>
            <div class="col-6">
              <div class="fw-semibold small text-uppercase">Phone</div>
              <div class="text-muted">{{ $phone }}</div>
            </div>
            <div class="col-6">
              <div class="fw-semibold small text-uppercase">Birthday</div>
              <div class="text-muted">{{ $birthday }}</div>
            </div>
            <div class="col-6">
              <div class="fw-semibold small text-uppercase">Age</div>
              <div class="text-muted">{{ $age }}</div>
            </div>
          </div>
        </div>
      </div>
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
      @endif
    </div>

    <div class="col-lg-6">
      <div class="row g-3">
        <div class="col-12">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h6 class="card-title">Edit Username</h6>

            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h6 class="card-title">Change Password</h6>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
