@extends('layouts.adminlayout')

@section('title', 'Add Senior Citizen Beneficiary')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold m-0">
            <i class="bi bi-person-plus me-2"></i> Add Senior Citizen Beneficiary
        </h2>

        {{-- BACK BUTTON --}}
        <a href="{{ route('senior-citizen.view', ['encryptedBarangayId' => encrypt($barangay->id)]) }}" class="btn btn-secondary rounded-3 fw-semibold">
            <i class="bi bi-arrow-left me-2"></i> Back
        </a>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm rounded-3">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm rounded-3">{{ session('success') }}</div>
    @endif

    <div class="row g-4">

        {{-- LEFT COLUMN - MANUAL FORM --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4">

                <div class="card-header bg-primary text-white py-3 rounded-top-4">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-pencil-square me-2"></i> Senior Citizen Information
                    </h5>
                </div>

                <div class="card-body p-4">

                    <form method="POST" action="{{ route('beneficiaries.store') }}" id="addBeneficiaryForm">
                        @csrf

                        <div class="row g-3">

                            {{-- NAME FIELDS --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" id="last_name" class="form-control rounded-3"
                                       value="{{ old('last_name') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" id="first_name" class="form-control rounded-3"
                                       value="{{ old('first_name') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Middle Name</label>
                                <input type="text" name="middle_name" id="middle_name" class="form-control rounded-3"
                                       value="{{ old('middle_name') }}">
                            </div>

                            {{-- BIRTHDAY / AGE / GENDER --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Birthday <span class="text-danger">*</span></label>
                                <input type="date" name="birthday" id="birthday" class="form-control rounded-3"
                                       value="{{ old('birthday') }}" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Age <span class="text-danger">*</span></label>
                                <input type="number" name="age" id="age" class="form-control rounded-3"
                                       value="{{ old('age') }}" readonly required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                <select name="gender" id="gender" class="form-select rounded-3" required>
                                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Civil Status <span class="text-danger">*</span></label>

                                {{-- select existing values --}}
                                <select id="civilStatusSelect" class="form-select mb-2">
                                    <option value="">-- Select existing (optional) --</option>
                                    @if(!empty($civilStatuses))
                                        @foreach($civilStatuses as $cs)
                                            <option value="{{ $cs }}">{{ $cs }}</option>
                                        @endforeach
                                    @endif
                                </select>

                                {{-- free-text input (required) --}}
                                <input type="text" name="civil_status" id="civil_status" class="form-control rounded-3" value="{{ old('civil_status') }}" required>
                            </div>

                            {{-- ID DETAILS --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">OSCA Number <span class="text-danger">*</span></label>
                                <input type="text" name="osca_number" id="osca_number"
                                       class="form-control rounded-3" value="{{ old('osca_number') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date Issued <span class="text-danger">*</span></label>
                                <input type="date" name="date_issued" id="date_issued"
                                       class="form-control rounded-3" value="{{ old('date_issued') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Remarks</label>

                                {{-- select existing remarks --}}
                                <select id="remarksSelect" class="form-select mb-2">
                                    <option value="">-- Select existing (optional) --</option>
                                    @if(!empty($remarks))
                                        @foreach($remarks as $r)
                                            <option value="{{ $r }}">{{ $r }}</option>
                                        @endforeach
                                    @endif
                                </select>

                                {{-- free-text remarks --}}
                                <input type="text" name="remarks" id="remarks" class="form-control rounded-3" value="{{ old('remarks') }}">
                            </div>

                            {{-- OTHER ID DETAILS --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">National ID</label>
                                <input type="text" name="national_id" id="national_id"
                                       class="form-control rounded-3" value="{{ old('national_id') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">PKN</label>
                                <input type="text" name="pkn" id="pkn"
                                       class="form-control rounded-3" value="{{ old('pkn') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">RRN</label>
                                <input type="text" name="rrn" id="rrn"
                                       class="form-control rounded-3" value="{{ old('rrn') }}">
                            </div>

                            {{-- BARANGAY --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Barangay <span class="text-danger">*</span></label>
                                <input type="text" name="barangay_name" id="barangay_name"
                                       class="form-control rounded-3" value="{{ $barangay->barangay_name }}" readonly>
                                <input type="hidden" name="barangay_id" value="{{ encrypt($barangay->id) }}">
                            </div>

                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 rounded-3 fw-semibold">
                                <i class="bi bi-save me-2"></i> Add Beneficiary
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN - CSV IMPORT --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4">

                <div class="card-header bg-success text-white py-3 rounded-top-4">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-file-earmark-arrow-up me-2"></i> Import Beneficiaries (CSV)
                    </h5>
                </div>

                <div class="card-body p-4">

                    {{-- Template Download --}}
                    <a href="{{ route('senior-citizen.download-template') }}" class="btn btn-info w-100 mb-3 rounded-3 fw-semibold">
                        Download CSV Template
                    </a>

                    <form id="importForm" method="POST" enctype="multipart/form-data"
                          action="{{ route('beneficiaries.import') }}">
                        @csrf

                        <input type="hidden" name="encrypted_barangay_id" value="{{ encrypt($barangay->id) }}">

                        <label class="form-label fw-semibold">Upload CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="csv_file" id="csv_file"
                               class="form-control rounded-3 mb-3" accept=".csv" required>

                        <button type="submit" class="btn btn-success w-100 rounded-3 fw-semibold">
                            <i class="bi bi-upload me-2"></i> Import CSV
                        </button>
                    </form>

                    {{-- Progress UI --}}
                    <div id="progressContainer" class="mt-4" style="display: none;">
                        <div class="progress" style="height: 8px;">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                        <p id="progressMessage" class="mt-2 small"></p>
                    </div>

                    {{-- Undo Import Button --}}
                    <div class="mt-2">
                        <button type="button" id="undoImportBtn" class="btn btn-warning btn-sm" style="display:none;">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Undo Last Import
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csSelect = document.getElementById('civilStatusSelect');
    const csInput = document.getElementById('civil_status');
    if (csSelect && csInput) {
        csSelect.addEventListener('change', function () {
            if (this.value) csInput.value = this.value;
        });
    }

    const remarksSelect = document.getElementById('remarksSelect');
    const remarksInput = document.getElementById('remarks');
    if (remarksSelect && remarksInput) {
        remarksSelect.addEventListener('change', function () {
            if (this.value) remarksInput.value = this.value;
        });
    }
});
</script>

<script>
    const importRoute = "{{ route('beneficiaries.import') }}";
</script>
<script src="{{ asset('js/add-seniorbeneficiaries.js') }}"></script>
@endpush
