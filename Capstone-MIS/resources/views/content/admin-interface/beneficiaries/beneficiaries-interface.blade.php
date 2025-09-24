@extends('layouts.adminlayout')

@section('title', 'Beneficiaries')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <!-- Senior Members Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <a href="{{ route('senior-citizen.interface') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <img src="{{ asset('img/senior-card.jpg') }}" class="card-img-top" alt="Senior Members" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center">
                        <i class="fas fa-user-tie fa-3x text-primary mb-3"></i>
                        <h5 class="card-title text-dark">Senior Members</h5>
                        <p class="card-text text-muted">View and manage all senior members.</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- PWD Members Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <a href="{{ route('beneficiaries.pwds') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <img src="{{ asset('img/pwd-card.jpg') }}" class="card-img-top" alt="PWD Members" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center">
                        <i class="fas fa-wheelchair fa-3x text-success mb-3"></i>
                        <h5 class="card-title text-dark">PWD Members</h5>
                        <p class="card-text text-muted">View and manage all PWD members.</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Registered Senior Citizens Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm h-100 hover-card">
                <img src="{{ asset('img/senior-card.jpg') }}" class="card-img-top" alt="Registered Senior Citizens" style="height: 200px; object-fit: cover;">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-info mb-3"></i>
                    <h5 class="card-title text-dark">Registered Senior Citizens</h5>
                    <p class="card-text text-muted">Total: <strong>Blank</strong></p>
                </div>
            </div>
        </div>

        <!-- Registered PWDs Card -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm h-100 hover-card">
                <img src="{{ asset('img/pwd-card.jpg') }}" class="card-img-top" alt="Registered PWDs" style="height: 200px; object-fit: cover;">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-warning mb-3"></i>
                    <h5 class="card-title text-dark">Registered PWDs</h5>
                    <p class="card-text text-muted">Total: <strong>Blank</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

