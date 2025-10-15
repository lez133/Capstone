@extends('layouts.adminlayout')

@section('title', 'MSWD Dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mswddash.css') }}">

<!-- 🧭 Aid Programs Carousel -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header text-white d-flex align-items-center">
                <i class="bi bi-box-seam me-2 fs-4"></i>
                <h5 class="mb-0">Aid Programs</h5>
            </div>
            @if ($aidPrograms->isEmpty())
                <div class="card-body text-center py-5">
                    <i class="bi bi-info-circle text-muted" style="font-size: 2.5rem;"></i>
                    <p class="text-muted mt-2">No aid programs available at the moment.</p>
                </div>
            @else
                <div id="aidProgramsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                    <div class="carousel-indicators">
                        @foreach ($aidPrograms as $index => $program)
                            <button type="button" data-bs-target="#aidProgramsCarousel" data-bs-slide-to="{{ $index }}"
                                    class="{{ $index === 0 ? 'active' : '' }}"
                                    aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                                    aria-label="Slide {{ $index + 1 }}">
                            </button>
                        @endforeach
                    </div>

                    <div class="carousel-inner rounded">
                        @foreach ($aidPrograms as $index => $program)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img src="{{ $program->background_image ? asset('storage/' . $program->background_image) : asset('img/default-placeholder.jpg') }}"
                                     class="d-block w-100" alt="{{ $program->aid_program_name }}">
                                <div class="carousel-caption p-3">
                                    <h5 class="fw-bold text-white mb-1">{{ $program->aid_program_name }}</h5>
                                    <p class="d-none d-sm-block text-white-50">{{ Str::limit($program->description, 100) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#aidProgramsCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#aidProgramsCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 📊 Quick Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4 col-sm-6">
        <div class="card stat-card text-center shadow-sm border-0 py-4 card-bg-primary">
            <i class="bi bi-people-fill text-primary"></i>
            <p class="text-muted mt-2 mb-1">Total Beneficiaries</p>
            <h4 class="fw-bold">{{ $totalBeneficiaries }}</h4>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="card stat-card text-center shadow-sm border-0 py-4 card-bg-success">
            <i class="bi bi-building text-success"></i>
            <p class="text-muted mt-2 mb-1">Total Barangays</p>
            <h4 class="fw-bold">{{ $totalBarangays }}</h4>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="card stat-card text-center shadow-sm border-0 py-4 card-bg-warning">
            <i class="bi bi-boxes text-warning"></i>
            <p class="text-muted mt-2 mb-1">Total Aid Programs</p>
            <h4 class="fw-bold">{{ $totalAidPrograms }}</h4>
        </div>
    </div>
</div>

<!-- 🧑 Recent Beneficiaries -->
<div class="row g-3">
    <div class="col-12">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header text-white d-flex align-items-center">
                <i class="bi bi-person-lines-fill me-2 fs-4"></i>
                <h5 class="mb-0">Recent Beneficiaries</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
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
                                <td class="fw-semibold">{{ $beneficiary->last_name }}, {{ $beneficiary->first_name }}</td>
                                <td>{{ $beneficiary->barangay->barangay_name ?? 'N/A' }}</td>
                                <td>{{ $beneficiary->age }}</td>
                                <td>{{ $beneficiary->gender }}</td>
                                <td>{{ $beneficiary->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted text-center py-4">No recent beneficiaries found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
