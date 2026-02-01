@extends('layouts.brgylayout')

@section('title', 'Submit Document Requirement')

@section('content')
<div class="container-fluid py-4">
    <!-- Statistics Cards -->
    <div class="row mb-4 g-3">
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-3 shadow-sm h-100">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small mb-2">Total Requests</p>
                    <h2 class="fw-bold text-dark">{{ $stats['total'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-3 shadow-sm h-100">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small mb-2">Pending</p>
                    <h2 class="fw-bold text-warning">{{ $stats['pending'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-3 shadow-sm h-100">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small mb-2">In Progress</p>
                    <h2 class="fw-bold text-info">{{ $stats['in_progress'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-3 shadow-sm h-100">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small mb-2">Completed</p>
                    <h2 class="fw-bold text-success">{{ $stats['completed'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card border-0 rounded-3 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="fas fa-file-upload me-2"></i>Submit Document Requirement
                    </h4>
                    <p class="text-muted small mb-0">Submit document requirements on behalf of beneficiaries</p>
                </div>
                <button type="button" class="btn btn-dark rounded-3 fw-semibold w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#submitDocumentModal">
                    <i class="fas fa-plus me-2"></i>New Submission
                </button>
            </div>

            <!-- Error & Success Messages -->
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Error!</strong>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Search & Filter -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-9">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-0 rounded-start-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               class="form-control bg-light border-0 rounded-end-3"
                               id="searchDocuments"
                               placeholder="Search by beneficiary name or ID...">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select form-select-lg rounded-3" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="pending">Pending Review</option>
                        <option value="progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>

            <!-- Beneficiaries List with View Submitted Documents -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Beneficiary Name</th>
                            <th>Beneficiary Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $uniqueBeneficiaries = collect($recentDocuments ?? [])
                                ->pluck('beneficiary')
                                ->unique('id')
                                ->values();
                        @endphp
                        @forelse($uniqueBeneficiaries as $beneficiary)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $beneficiary->first_name }} {{ $beneficiary->last_name }}
                                </td>
                                <td>
                                    {{ $beneficiary->beneficiary_type ?? 'N/A' }}
                                </td>
                                <td>
                                    <a href="{{ route('brgyrep.beneficiary.documents', $beneficiary->id) }}"
                                       class="btn btn-sm btn-outline-primary rounded-3 w-100 w-md-auto">
                                        <i class="fas fa-eye me-1"></i>View Submitted Documents
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No beneficiaries with submitted documents yet
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Responsive Modal -->
<div class="modal fade" id="submitDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content border-0 rounded-3">
            <div class="modal-header border-0 bg-light rounded-top-3 py-2 px-3">
                <h5 class="modal-title fw-bold fs-6">
                    <i class="fas fa-file-upload me-2"></i>Submit Document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('brgyrep.submit-document.store') }}" method="POST" enctype="multipart/form-data" id="submitForm">
                @csrf

                <div class="modal-body p-2 p-md-3">
                    <div class="row g-2">
                        <div class="col-12">
                            <label for="beneficiary_id" class="form-label fw-semibold small">
                                <i class="fas fa-user me-2 text-primary"></i>Beneficiary
                            </label>
                            <select name="beneficiary_id" id="beneficiary_id" class="form-select form-select-sm rounded-3" required>
                                <option value="">-- Select Beneficiary --</option>
                                <optgroup label="Senior Citizens">
                                    @foreach($seniorCitizens as $beneficiary)
                                        <option value="{{ $beneficiary->id }}">
                                            {{ $beneficiary->first_name }} {{ $beneficiary->last_name }} (OSCA: {{ $beneficiary->osca_number }})
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="PWD">
                                    @foreach($pwdList as $beneficiary)
                                        <option value="{{ $beneficiary->id }}">
                                            {{ $beneficiary->first_name }} {{ $beneficiary->last_name }} (PWD ID: {{ $beneficiary->pwd_id }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="modal_aid_type" class="form-label fw-semibold small mt-2">
                                <i class="fas fa-tag me-2 text-primary"></i>Aid Program
                            </label>
                            <select name="aid_type" id="modal_aid_type" class="form-select form-select-sm" onchange="fetchRequirements(this.value)">
                                <option value="">-- Select Aid Program --</option>
                                @foreach($aidPrograms as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="modal_requirement" class="form-label fw-semibold small mt-2">
                                <i class="fas fa-file me-2 text-primary"></i>Requirement
                            </label>
                            <select name="requirement" id="modal_requirement" class="form-select form-select-sm mt-2">
                                <option value="">-- Select Requirement --</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="document_file" class="form-label fw-semibold small mt-2">
                                <i class="fas fa-paperclip me-2 text-primary"></i>Upload Document
                            </label>
                            <input type="file" name="document_file" id="document_file" class="form-control form-control-sm rounded-3" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx" required>
                            <small class="text-muted">Max 5MB</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 bg-light rounded-bottom-3 d-flex flex-column flex-md-row gap-2 px-2 py-2">
                    <button type="button" class="btn btn-outline-secondary rounded-3 fw-semibold w-100" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnSubmit" class="btn btn-primary rounded-3 fw-semibold w-100">
                        <i class="fas fa-paper-plane me-2"></i>Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function fetchRequirements(aidTypeId) {
    const reqSelect = document.getElementById('modal_requirement');
    reqSelect.innerHTML = '<option value="">Loading...</option>';
    if (!aidTypeId) {
        reqSelect.innerHTML = '<option value="">-- Select Requirement --</option>';
        return;
    }
    fetch("{{ route('brgyrep.requirements') }}?aid_type=" + aidTypeId)
        .then(response => response.json())
        .then(data => {
            reqSelect.innerHTML = '';
            if (data.requirements && Object.keys(data.requirements).length) {
                for (const [id, label] of Object.entries(data.requirements)) {
                    reqSelect.innerHTML += `<option value="${id}">${label}</option>`;
                }
            } else {
                reqSelect.innerHTML = '<option value="">No requirements found</option>';
            }
        })
        .catch(() => {
            reqSelect.innerHTML = '<option value="">Error loading requirements</option>';
        });
}
</script>
@endsection
