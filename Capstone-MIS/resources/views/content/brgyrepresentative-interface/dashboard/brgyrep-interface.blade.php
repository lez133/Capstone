@extends('layouts.brgylayout')

@section('title', 'Barangay Representative Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 g-3">
        <!-- Total Beneficiaries Card -->
        <div class="col-md-4">
            <div class="card border-0 rounded-3 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-muted small mb-2">Total Beneficiaries</p>
                    <h2 class="fw-bold text-dark">{{ $data['total_beneficiaries'] ?? 0 }}</h2>
                </div>
            </div>
        </div>

        <!-- PWDs Card -->
        <div class="col-md-4">
            <div class="card border-0 rounded-3 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-muted small mb-2">PWDs</p>
                    <h2 class="fw-bold text-dark">{{ $data['pwds_count'] ?? 0 }}</h2>
                </div>
            </div>
        </div>

        <!-- Senior Citizens Card -->
        <div class="col-md-4">
            <div class="card border-0 rounded-3 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-muted small mb-2">Senior Citizens</p>
                    <h2 class="fw-bold text-dark">{{ $data['senior_citizens_count'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Beneficiary Records Section -->
    <div class="card border-0 rounded-3 shadow-sm">
        <div class="card-body p-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold mb-1">Beneficiary Records</h5>
                    <p class="text-muted small mb-0">Manage PWD and Senior Citizen registrations</p>
                </div>
                <a href="#" class="btn btn-dark rounded-3 fw-semibold">
                    <i class="fas fa-plus me-2"></i>New Registration
                </a>
            </div>

            <!-- Search and Filter -->
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-0 rounded-start-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               class="form-control bg-light border-0 rounded-end-3"
                               id="searchBeneficiaries"
                               placeholder="Search by name or ID...">
                    </div>
                </div>
                <div class="col-md-4">
                    <form method="GET" id="filterForm">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="beneficiary_type" id="filterAll" value="all"
                                   @if(request('beneficiary_type', 'all') === 'all') checked @endif onchange="document.getElementById('filterForm').submit();">
                            <label class="btn btn-outline-dark rounded-start-3" for="filterAll">All</label>

                            <input type="radio" class="btn-check" name="beneficiary_type" id="filterPWD" value="pwd"
                                   @if(request('beneficiary_type') === 'pwd') checked @endif onchange="document.getElementById('filterForm').submit();">
                            <label class="btn btn-outline-dark" for="filterPWD">PWD</label>

                            <input type="radio" class="btn-check" name="beneficiary_type" id="filterSenior" value="senior"
                                   @if(request('beneficiary_type') === 'senior') checked @endif onchange="document.getElementById('filterForm').submit();">
                            <label class="btn btn-outline-dark rounded-end-3" for="filterSenior">Senior</label>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Beneficiary List -->
            <div class="beneficiary-list">
                @forelse($beneficiaries ?? [] as $beneficiary)
                    <div class="beneficiary-item p-3 mb-3 border rounded-3 bg-light">
                        <div class="row align-items-center g-3">
                            <div class="col-md-8">
                                <div class="d-flex gap-2 align-items-start mb-2">
                                    <h6 class="fw-bold mb-0">{{ $beneficiary->first_name }} {{ $beneficiary->last_name }}</h6>
                                    @if($beneficiary->beneficiary_type === 'Senior Citizen')
                                        <span class="badge bg-secondary">Senior Citizen</span>
                                    @else
                                        <span class="badge bg-info">PWD</span>
                                    @endif
                                    @if($beneficiary->verified)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Incomplete</span>
                                    @endif
                                </div>
                                <p class="text-muted small mb-2">
                                    ID: <strong>
                                        @if($beneficiary->beneficiary_type === 'Senior Citizen')
                                            {{ $beneficiary->osca_number ?? 'N/A' }}
                                        @elseif($beneficiary->beneficiary_type === 'PWD')
                                            {{ $beneficiary->pwd_id ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </strong> •
                                    Age: <strong>{{ $beneficiary->age ?? 'N/A' }}</strong>
                                </p>
                                <p class="text-muted small mb-2">
                                    Email: {{ $beneficiary->email ?? 'No email provided' }}
                                </p>
                                <p class="text-muted small mb-0">
                                    Contact: {{ $beneficiary->phone ?? 'No contact' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-inbox text-muted fa-3x mb-3"></i>
                        <p class="text-muted">No beneficiaries found</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($beneficiaries ?? false)
                <div class="d-flex justify-content-center mt-4">
                    {{ $beneficiaries->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchBeneficiaries');
    const filterRadios = document.querySelectorAll('input[name="beneficiary_type"]');

    function filterBeneficiaries() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedType = document.querySelector('input[name="beneficiary_type"]:checked').value;
        const items = document.querySelectorAll('.beneficiary-item');

        items.forEach(item => {
            const name = item.querySelector('h6').textContent.toLowerCase();
            const type = item.querySelector('.badge.bg-info, .badge.bg-secondary').textContent.toLowerCase();

            const matchesSearch = name.includes(searchTerm);
            const matchesType = selectedType === 'all' ||
                               (selectedType === 'pwd' && type.includes('pwd')) ||
                               (selectedType === 'senior' && type.includes('senior'));

            item.style.display = matchesSearch && matchesType ? 'block' : 'none';
        });
    }

    searchInput.addEventListener('keyup', filterBeneficiaries);
    filterRadios.forEach(radio => radio.addEventListener('change', filterBeneficiaries));
});
</script>
@endpush
@endsection
