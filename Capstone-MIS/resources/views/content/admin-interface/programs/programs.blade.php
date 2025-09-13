@extends('layouts.adminlayout')

@section('title', 'Manage Programs/Events')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Manage Programs/Events</h5>
        </div>
        <div class="card-body">
            <p>Welcome to the Manage Programs/Events page. You can add, edit, and delete programs or events here.</p>
        </div>
    </div>

    <!-- Cards Section -->
    <div class="row mt-4 g-4">
        <!-- Add Barangay Card -->
        <div class="col-md-4">
            <div class="card hover-card shadow-sm card-appear card-delay-1">
                <div class="card-body text-center">
                    <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Add Barangay</h5>
                    <p class="card-text">Add new barangays to the system for better organization and management.</p>
                    <a href="{{ route('barangays.index') }}" class="btn btn-primary">Add Barangay</a>
                </div>
            </div>
        </div>

        <!-- Add Program Type Card -->
        <div class="col-md-4">
            <div class="card hover-card shadow-sm card-appear card-delay-2">
                <div class="card-body text-center">
                    <i class="fas fa-list-alt fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Add Program Type</h5>
                    <p class="card-text">Define and manage different types of programs or events.</p>
                    <a href="{{ route('program-types.index') }}" class="btn btn-success">Add Program Type</a>
                </div>
            </div>
        </div>

        <!-- Add Aid Program Card -->
        <div class="col-md-4">
            <div class="card hover-card shadow-sm card-appear card-delay-3">
                <div class="card-body text-center">
                    <i class="fas fa-hands-helping fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Add Aid Program</h5>
                    <p class="card-text">Create and manage aid programs to assist beneficiaries effectively.</p>
                    <a href="{{ route('aid-programs.index') }}" class="btn btn-warning">Add Aid Program</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
