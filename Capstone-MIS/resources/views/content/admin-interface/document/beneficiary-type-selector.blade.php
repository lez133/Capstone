@extends('layouts.adminlayout')

@section('title', 'Beneficiary Program')

@section('content')
<style>
.icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.bg-primary-light { background-color: rgba(13, 110, 253, 0.1); }
.bg-success-light { background-color: rgba(25, 135, 84, 0.1); }

.card-title { font-size: 1.2rem; }
.card-text { font-size: 0.95rem; color: #6c757d; }
</style>

<div class="container py-5">
    <a href="{{ route('document.barangay.selector') }}" class="btn btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    <h2 class="mb-5 fw-bold text-center">Select Beneficiary Type</h2>
    <div class="row justify-content-center g-4">
        <!-- Senior Citizen Card -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100 text-center hover-card card-delay-1 rounded-4 border-0">
                <div class="card-body d-flex flex-column align-items-center p-4">
                    <div class="icon-circle bg-primary-light">
                        <i class="fas fa-user fa-2x text-primary"></i>
                    </div>
                    <h5 class="card-title mt-2">Senior Citizen</h5>
                    <p class="card-text text-center">View documents submitted by senior citizens.</p>
                    <a href="{{ route('document.program.type.selector', ['barangay_id' => request('barangay_id'), 'beneficiary_type' => 'senior']) }}" class="btn btn-primary btn-sm px-4 mt-auto">Select Senior Citizen</a>
                </div>
            </div>
        </div>

        <!-- PWD Card -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100 text-center hover-card card-delay-2 rounded-4 border-0">
                <div class="card-body d-flex flex-column align-items-center p-4">
                    <div class="icon-circle bg-success-light">
                        <i class="fas fa-wheelchair fa-2x text-success"></i>
                    </div>
                    <h5 class="card-title mt-2">PWD</h5>
                    <p class="card-text text-center">View documents submitted by persons with disabilities.</p>
                    <a href="{{ route('document.program.type.selector', ['barangay_id' => request('barangay_id'), 'beneficiary_type' => 'pwd']) }}" class="btn btn-success btn-sm px-4 mt-auto">Select PWD</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
