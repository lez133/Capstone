@extends('layouts.adminlayout')

@section('title', 'MSWD Dashboard')

@section('content')
    <!-- Link your fixed CSS file -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <!-- Aid Programs Carousel -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Aid Programs</h5>
                </div>
                <div id="aidProgramsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                    <!-- Indicators/Dots -->
                    <div class="carousel-indicators">
                        @foreach ($aidPrograms as $index => $program)
                            <button type="button" data-bs-target="#aidProgramsCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>

                    <!-- Carousel Items -->
                    <div class="carousel-inner">
                        @foreach ($aidPrograms as $index => $program)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img src="{{ $program->background_image ? asset('storage/' . $program->background_image) : asset('img/default-placeholder.jpg') }}" class="d-block w-100" alt="{{ $program->aid_program_name }}">
                                <div class="carousel-caption d-md-block">
                                    <div class="caption-overlay">
                                        <h5 class="text-truncate">{{ $program->aid_program_name }}</h5>
                                        <p class="d-none d-sm-block">{{ Str::limit($program->description, 100) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Navigation Buttons -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#aidProgramsCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#aidProgramsCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-sm-6">
            <div class="card stat-card">
                <small class="text-muted">Total Beneficiaries</small>
                <div class="h4 fw-bold mt-1">{{ $totalBeneficiaries }}</div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card stat-card">
                <small class="text-muted">Total Barangays</small>
                <div class="h4 fw-bold mt-1">{{ $totalBarangays }}</div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card stat-card">
                <small class="text-muted">Total Aid Programs</small>
                <div class="h4 fw-bold mt-1">{{ $totalAidPrograms }}</div>
            </div>
        </div>
    </div>

    <!-- Recent Beneficiaries -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Recent Beneficiaries</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Barangay</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentBeneficiaries as $index => $beneficiary)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $beneficiary->last_name }}, {{ $beneficiary->first_name }}</td>
                                    <td>{{ $beneficiary->barangay->barangay_name ?? 'N/A' }}</td>
                                    <td>{{ $beneficiary->age }}</td>
                                    <td>{{ $beneficiary->gender }}</td>
                                    <td>{{ $beneficiary->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-muted text-center">No recent beneficiaries found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
