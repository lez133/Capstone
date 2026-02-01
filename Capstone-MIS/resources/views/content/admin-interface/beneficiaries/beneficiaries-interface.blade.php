@extends('layouts.adminlayout')

@section('title', 'Beneficiaries')

@section('content')
<div class="container mt-4">
    <div class="mb-3">
        <a href="{{ route('beneficiaries.select-barangay') }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back to Barangay Selection
        </a>
    </div>
    <div class="row justify-content-center">
        <!-- Senior Members Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <a href="{{ route('senior-citizen.view', ['encryptedBarangayId' => $selectedBarangay->encrypted_id ?? encrypt($selectedBarangay->id)]) }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <img src="{{ asset('img/senior-card.jpg') }}" class="card-img-top" alt="Senior Members" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center">
                        <i class="fas fa-user-tie fa-3x text-primary mb-3"></i>
                        <h5 class="card-title text-dark">Senior Members</h5>
                        <p class="card-text text-muted">
                            View and manage all senior members for <b>{{ $selectedBarangay->barangay_name ?? 'this barangay' }}.</b>
                        </p>

                        {{-- Counts: simplified + main total --}}
                        <div class="mt-2">
                            <span class="badge bg-primary me-1">Total (specialized): {{ $counts['total_senior_registered'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- PWD Members Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <a href="{{ route('pwd.view', ['encryptedBarangayId' => $selectedBarangay->encrypted_id ?? encrypt($selectedBarangay->id)]) }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <img src="{{ asset('img/pwd-card.jpg') }}" class="card-img-top" alt="PWD Members" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center">
                        <i class="fas fa-wheelchair fa-3x text-success mb-3"></i>
                        <h5 class="card-title text-dark">PWD Members</h5>
                        <p class="card-text text-muted">
                            View and manage all PWD members for <b>{{ $selectedBarangay->barangay_name ?? 'this barangay' }}.</b>
                        </p>

                        {{-- Counts: simplified + main total --}}
                        <div class="mt-2">
                            <span class="badge bg-primary me-1">Total (specialized): {{ $counts['total_pwd_registered'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Manage Registered Senior Citizens Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <a href="{{ route('senior-citizens.manage', ['encryptedBarangayId' => $selectedBarangay->encrypted_id ?? encrypt($selectedBarangay->id)]) }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <img src="{{ asset('img/senior-card.jpg') }}" class="card-img-top" alt="Registered Senior Citizens" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x text-info mb-3"></i>
                        <h5 class="card-title text-dark">Manage Registered Senior Citizens</h5>
                        <p class="card-text text-muted">Manage senior citizens for <b>{{ $selectedBarangay->barangay_name ?? 'this barangay' }}.</b></p>

                        {{-- Counts --}}
                        <div class="mt-2">
                            <span class="badge bg-success me-1">Verified: {{ $counts['verified_senior'] ?? 0 }}</span>
                            <span class="badge bg-danger me-1">Unverified: {{ $counts['unverified_senior'] ?? 0 }}</span>
                            <span class="badge bg-secondary">Total (main): {{ $counts['total_senior'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Registered PWDs Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <a href="{{ route('pwd.manage', ['encryptedBarangayId' => $selectedBarangay->encrypted_id ?? encrypt($selectedBarangay->id)]) }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <img src="{{ asset('img/pwd-card.jpg') }}" class="card-img-top" alt="Registered PWDs" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x text-warning mb-3"></i>
                        <h5 class="card-title text-dark">Manage Registered PWDs</h5>
                        <p class="card-text text-muted">Manage PWDs for <b>{{ $selectedBarangay->barangay_name ?? 'this barangay' }}.</b></p>

                        {{-- Counts --}}
                        <div class="mt-2">
                            <span class="badge bg-success me-1">Verified: {{ $counts['verified_pwd'] ?? 0 }}</span>
                            <span class="badge bg-danger me-1">Unverified: {{ $counts['unverified_pwd'] ?? 0 }}</span>
                            <span class="badge bg-secondary">Total (main): {{ $counts['total_pwd'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

