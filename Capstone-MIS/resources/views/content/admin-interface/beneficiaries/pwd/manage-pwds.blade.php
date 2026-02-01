@extends('layouts.adminlayout')

@section('title', 'Manage Beneficiaries')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Manage Beneficiaries</h1>

    <div class="mt-4">
        <a href="{{ route('beneficiaries.interface', ['encryptedBarangayId' => encrypt($barangay->id)]) }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Barangays
        </a>
    </div>

    <div class="row">
        <!-- Verified Beneficiaries Card -->
        <div class="col-md-6 mb-4">
            <a href="{{ route('pwd.verified', $encryptedBarangayId) }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="card-title text-dark">Verified Beneficiaries</h5>
                        <p class="card-text text-muted">View the list of verified beneficiaries.</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Not Verified Beneficiaries Card -->
        <div class="col-md-6 mb-4">
            <a href="{{ route('pwd.not-verified', $encryptedBarangayId) }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card position-relative">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                        <h5 class="card-title text-dark">Not Verified Beneficiaries</h5>
                        <p class="card-text text-muted">View the list of not verified beneficiaries.</p>
                    </div>

                    <!-- Always visible badge -->
                    <span class="position-absolute badge rounded-pill
                        {{ ($notVerifiedCount ?? 0) > 0 ? 'bg-danger' : 'bg-secondary' }}"
                        style="top: 10px; right: 15px; font-size: 1rem; padding: 6px 12px;">
                        {{ $notVerifiedCount ?? 0 }}
                    </span>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
