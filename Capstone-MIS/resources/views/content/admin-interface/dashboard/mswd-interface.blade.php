@extends('layouts.adminlayout')

@section('title', 'MSWD Dashboard')

@section('content')

<link rel="stylesheet" href="{{ asset('css/mswddash.css') }}">

<div x-data="{ loading: true }"
     x-init="setTimeout(() => loading = false, 1200)"
     x-cloak>

    <!-- 🧭 Aid Programs Carousel -->
    <div class="row g-3 mb-4" x-cloak>
        <div class="col-12">
            <div class="card h-100 shadow-lg border-0">
                <div class="card-header text-white d-flex align-items-center shadow-sm">
                    <i class="bi bi-box-seam me-2 fs-4"></i>

                    <template x-if="loading">
                        <div class="skeleton" style="height: 20px; width: 120px;"></div>
                    </template>

                    <h5 class="mb-0" x-show="!loading" x-cloak>Aid Programs</h5>
                </div>

                <template x-if="loading">
                    <div class="card-body">
                        <div class="skeleton mb-3" style="height: 220px; width: 100%;"></div>
                    </div>
                </template>

                <div x-show="!loading" x-cloak>
                    @if ($aidPrograms->isEmpty())
                        <div class="card-body text-center py-5">
                            <i class="bi bi-info-circle text-muted" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mt-2">No aid programs available at the moment.</p>
                        </div>
                    @else
                        <div id="aidProgramsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">
                            <div class="carousel-indicators">
                                @foreach ($aidPrograms as $index => $program)
                                    <button type="button"
                                            data-bs-target="#aidProgramsCarousel"
                                            data-bs-slide-to="{{ $index }}"
                                            class="{{ $index === 0 ? 'active' : '' }}"
                                            aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                                            aria-label="Slide {{ $index + 1 }}">
                                    </button>
                                @endforeach
                            </div>

                            <div class="carousel-inner rounded">
                                @foreach ($aidPrograms as $index => $program)
                                    @php
                                        // Use custom uploaded image if available,
                                        // else use selected default background,
                                        // else use default-placeholder
                                        $bgImg = $program->background_image
                                            ? asset('storage/' . $program->background_image)
                                            : ($program->default_background
                                                ? asset('img/' . $program->default_background)
                                                : asset('img/default-placeholder.jpg'));
                                    @endphp
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ $bgImg }}" class="d-block w-100">
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
    </div>

    <!-- 📊 Quick Stats -->
    <div class="row g-3 mb-4" x-cloak>
        <template x-if="loading">
            <template x-for="i in 5" :key="i">
                <div class="col-12 col-sm-6 col-lg">
                    <div class="card stat-card text-center shadow-lg border-0 py-4 h-100">
                        <div class="skeleton mx-auto mb-2" style="height: 32px; width: 32px;"></div>
                        <div class="skeleton mx-auto mb-2" style="height: 16px; width: 80px;"></div>
                        <div class="skeleton mx-auto" style="height: 28px; width: 60px;"></div>
                    </div>
                </div>
            </template>
        </template>

        <div class="contents row g-3 w-100" x-show="!loading" x-cloak>
            <div class="col-12 col-sm-6 col-lg">
                <a href="{{ route('total-beneficiaries') }}" class="text-decoration-none">
                    <div class="card stat-card text-center shadow-lg border-0 py-4 card-bg-primary h-100">
                        <i class="bi bi-people-fill text-primary"></i>
                        <p class="text-muted mt-2 mb-1">Total Beneficiaries</p>
                        <h4 class="fw-bold">{{ $totalBeneficiaries }}</h4>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-lg">
                <a href="{{ route('beneficiaries.select-barangay') }}" class="text-decoration-none">
                    <div class="card stat-card text-center shadow-lg border-0 py-4 card-bg-success h-100">
                        <i class="bi bi-building text-success"></i>
                        <p class="text-muted mt-2 mb-1">Total Barangays</p>
                        <h4 class="fw-bold">{{ $totalBarangays }}</h4>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-lg">
                <a href="{{ route('aid-programs.index') }}" class="text-decoration-none">
                    <div class="card stat-card text-center shadow-lg border-0 py-4 card-bg-warning h-100">
                        <i class="bi bi-boxes text-warning"></i>
                        <p class="text-muted mt-2 mb-1">Total Aid Programs</p>
                        <h4 class="fw-bold">{{ $totalAidPrograms }}</h4>
                    </div>
                </a>
            </div>


            <div class="col-12 col-sm-6 col-lg">
                <a href="{{ route('verified-beneficiaries') }}" class="text-decoration-none">
                    <div class="card stat-card text-center shadow-lg border-0 py-4 card-bg-info h-100">
                        <i class="bi bi-person-badge text-info"></i>
                        <p class="text-muted mt-2 mb-1">Verified Registered Accounts</p>
                        <h4 class="fw-bold">{{ $totalVerifiedRegistered ?? 0 }}</h4>
                    </div>
                </a>
            </div>


            <div class="col-12 col-sm-6 col-lg">
                <a href="{{ route('unverified-beneficiaries') }}" class="text-decoration-none">
                    <div class="card stat-card text-center shadow-lg border-0 py-4 card-bg-danger h-100">
                        <i class="bi bi-person-x text-danger"></i>
                        <p class="text-muted mt-2 mb-1">Unverified Registered Accounts</p>
                        <h4 class="fw-bold">{{ $totalUnverifiedRegistered ?? 0 }}</h4>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- 🗂️ Collapsible Tables -->
    <div class="row g-3" x-cloak>
        @php
            $tables = [
                ['title' => 'Recent PWD Beneficiaries', 'data' => $recentPWDBeneficiaries, 'cols' => 7],
                ['title' => 'Recent Senior Citizen Beneficiaries', 'data' => $recentSeniorBeneficiaries, 'cols' => 6],
                ['title' => 'Recent Registered Beneficiaries (Verified)', 'data' => $recentVerifiedRegistered, 'cols' => 8],
                ['title' => 'Recent Registered Beneficiaries (Unverified)', 'data' => $recentUnverifiedRegistered, 'cols' => 8],
            ];
        @endphp

        @foreach ($tables as $index => $table)
            <div class="col-12 col-lg-6" x-data="{ open: false }" x-cloak>
                <div class="card shadow-lg border-0">

                    <div class="card-header fw-bold d-flex justify-content-between align-items-center">

                        <template x-if="loading">
                            <div class="skeleton" style="height: 20px; width: 220px;"></div>
                        </template>

                        <span x-show="!loading" x-cloak>{{ $table['title'] }}</span>

                        <button class="btn btn-sm px-3 py-1 btn-primary rounded-pill d-flex align-items-center gap-1"
                                @click="open = !open">
                            <span x-text="open ? 'Hide' : 'Show'"></span>
                            <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </button>
                    </div>

                    <div x-show="open" x-collapse x-cloak>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle mb-0" style="font-size: 0.95rem;">
                                <thead class="table-light">
                                    @if($index === 0)
                                        <tr>
                                            <th>Name</th><th>Barangay</th><th>Gender</th>
                                            <th>Disability</th><th>Birthday</th><th>Age</th><th>Date Added</th>
                                        </tr>
                                    @elseif($index === 1)
                                        <tr>
                                            <th>Name</th><th>Barangay</th><th>Gender</th>
                                            <th>Birthday</th><th>Age</th><th>Date Added</th>
                                        </tr>
                                    @else
                                        <tr>
                                            <th>Name</th>
                                            <th>Barangay</th>
                                            <th>Email</th>
                                            <th>Beneficiary Type</th>
                                            <th>Gender</th>
                                            <th>Birthday</th>
                                            <th>Age</th>
                                            <th>Date Registered</th>
                                        </tr>
                                    @endif
                                </thead>

                                <tbody>
                                    <template x-if="loading">
                                        <template x-for="i in 5">
                                            <tr>
                                                @for($j = 0; $j < $table['cols']; $j++)
                                                    <td><div class="skeleton" style="height: 16px; width: 100%;"></div></td>
                                                @endfor
                                            </tr>
                                        </template>
                                    </template>

                                    @forelse($table['data'] as $b)
                                        <tr x-show="!loading" x-cloak>
                                            <td>{{ $b->last_name }}, {{ $b->first_name }} {{ $b->middle_name }}</td>
                                            <td>{{ $b->barangay->barangay_name ?? 'N/A' }}</td>

                                            @if($index < 2)
                                                <td>{{ $b->gender }}</td>
                                                @if($index === 0)
                                                    <td>{{ $b->type_of_disability }}</td>
                                                @endif
                                                <td>{{ $b->birthday ?? 'N/A' }}</td>
                                                <td>{{ $b->age ?? 'N/A' }}</td>
                                                <td>{{ $b->created_at->format('M d, Y') }}</td>
                                            @else
                                                <td>{{ $b->email }}</td>
                                                <td>{{ $b->beneficiary_type ?? 'N/A' }}</td>
                                                <td>{{ $b->gender }}</td>
                                                <td>{{ $b->birthday }}</td>
                                                <td>{{ $b->age }}</td>
                                                <td>{{ $b->created_at->format('M d, Y') }}</td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr x-show="!loading" x-cloak>
                                            <td colspan="{{ $table['cols'] }}" class="text-muted text-center">
                                                No data available.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>

</div>

@endsection
