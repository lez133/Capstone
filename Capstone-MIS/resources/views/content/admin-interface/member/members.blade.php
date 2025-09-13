@extends('layouts.adminlayout')

@section('title', 'Members')

@section('content')
<div class="container mt-4">
    <!-- Add Member button -->
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('members.create') }}" class="btn btn-success">
            <i class="fas fa-user-plus me-2"></i>Add Member
        </a>
    </div>
    <!-- MSWD Card -->
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-6 mb-4">
            <a href="{{ route('members.mswd') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <img src="{{ asset('img/mswd-card.jpg') }}" class="card-img-top" alt="MSWD Members" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center">
                        <i class="fas fa-user-tie fa-3x text-primary mb-3"></i>
                        <h5 class="card-title text-dark">MSWD Members</h5>
                        <p class="card-text text-muted">View and manage all MSWD members.</p>
                    </div>
                </div>
            </a>
        </div>
        <!-- Brgy Members Card -->
        <div class="col-md-6 col-lg-6 mb-4">
            <a href="{{ route('members.brgy') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <img src="{{ asset('img/member-card.jpg') }}" class="card-img-top" alt="Barangay Representatives" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x text-success mb-3"></i>
                        <h5 class="card-title text-dark">Barangay Representatives</h5>
                        <p class="card-text text-muted">View and manage all Barangay Representatives.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

@endsection
