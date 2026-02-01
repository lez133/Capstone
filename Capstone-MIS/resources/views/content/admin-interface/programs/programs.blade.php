@extends('layouts.adminlayout')

@section('title', 'Manage Programs/Events')

@section('content')
<style>
.icon-circle {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.bg-primary-light { background-color: rgba(13, 110, 253, 0.1); }
.bg-success-light { background-color: rgba(25, 135, 84, 0.1); }
.bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
.bg-info-light    { background-color: rgba(13, 202, 240, 0.1); }
.bg-secondary-light { background-color: rgba(108, 117, 125, 0.1); }

.card-title { font-size: 1.1rem; }
.card-text { font-size: 0.95rem; }

/* Enhanced shadow & hover lift */
.card-hover-shadow {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card-hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
}
</style>

<div class="container mt-4">
    <!-- Header Card -->
    <div class="card shadow-lg rounded-4 border-0 overflow-hidden">
        <div class="card-header text-white p-4" style="background: linear-gradient(135deg, #4e73df, #1cc88a); position: relative;">
            <h4 class="mb-1 fw-bold">Manage Programs/Events</h4>
            <p class="mb-0 text-white-50">Welcome! Here you can add, edit, and manage programs, events, barangays, schedules, and requirements efficiently.</p>
            <i class="fas fa-tasks fa-3x opacity-25" style="position: absolute; top: 10px; right: 15px;"></i>
        </div>
        <div class="card-body bg-white p-4">
            <div class="row">
                <div class="col">
                    <p class="mb-0 text-muted">Use the cards below to navigate quickly to different sections like Barangays, Program Types, Aid Programs, Schedules, and Requirements.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards Section -->
    <div class="row mt-4 g-4">
        <!-- Add Barangay Card -->
        <div class="col-md-4">
            <div class="card hover-card card-hover-shadow card-appear card-delay-1 rounded-3 border-0">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-primary-light mb-3">
                        <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                    </div>
                    <h5 class="card-title fw-bold">Add Barangay</h5>
                    <p class="card-text text-muted">Add new barangays to the system for better organization and management.</p>
                    <a href="{{ route('barangays.index') }}" class="btn btn-primary btn-sm px-4">Add Barangay</a>
                </div>
            </div>
        </div>

        <!-- Add Program Type Card -->
        <div class="col-md-4">
            <div class="card hover-card card-hover-shadow card-appear card-delay-2 rounded-3 border-0">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-success-light mb-3">
                        <i class="fas fa-list-alt fa-2x text-success"></i>
                    </div>
                    <h5 class="card-title fw-bold">Add Program Type</h5>
                    <p class="card-text text-muted">Define and manage different types of programs or events.</p>
                    <a href="{{ route('program-types.index') }}" class="btn btn-success btn-sm px-4">Add Program Type</a>
                </div>
            </div>
        </div>

        <!-- Add Aid Program Card -->
        <div class="col-md-4">
            <div class="card hover-card card-hover-shadow card-appear card-delay-3 rounded-3 border-0">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-warning-light mb-3">
                        <i class="fas fa-hands-helping fa-2x text-warning"></i>
                    </div>
                    <h5 class="card-title fw-bold">Add & View Aid Program</h5>
                    <p class="card-text text-muted">Create and manage aid programs to assist beneficiaries effectively.</p>
                    <a href="{{ route('aid-programs.index') }}" class="btn btn-warning btn-sm px-4">Add Aid Program</a>
                </div>
            </div>
        </div>

        <!-- Add Requirement Card -->
        <div class="col-md-4">
            <div class="card hover-card card-hover-shadow card-appear card-delay-5 rounded-3 border-0">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-secondary-light mb-3">
                        <i class="fas fa-file-alt fa-2x text-secondary"></i>
                    </div>
                    <h5 class="card-title fw-bold">Create Requirement</h5>
                    <p class="card-text text-muted">Add new requirements for aid programs and manage existing ones.</p>
                    <a href="{{ route('requirements.index') }}" class="btn btn-secondary btn-sm px-4">Create Requirement</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
