@extends('layouts.adminlayout')

@section('title', 'Add PWD Beneficiary')

@section('content')
<div class="container py-4">

    {{-- ✅ Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle me-2"></i>{!! session('error') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ✅ Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ✅ Card Container --}}
    <div class="card border-0 shadow-lg rounded-4">
        <div class="card-body p-4">

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-primary mb-0">
                    <i class="bi bi-person-plus me-2"></i> Add PWD Beneficiary
                </h4>
                <div>
                    @if(isset($barangay))
                        <a href="{{ route('pwd.view', ['encryptedBarangayId' => Crypt::encrypt($barangay->id)]) }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    @elseif(isset($barangays) && $barangays->count() === 1)
                        <a href="{{ route('pwd.view', ['encryptedBarangayId' => Crypt::encrypt($barangays->first()->id)]) }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    @else
                        <a href="{{ route('pwd.interface') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    @endif
                </div>
            </div>

            {{-- ✅ Add Beneficiary Form --}}
            <form method="POST" action="{{ route('pwd.store') }}" class="needs-validation" novalidate>
                @csrf

                {{-- Name Fields --}}
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="first_name" class="form-label fw-semibold">First Name<span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control shadow-sm" required>
                    </div>
                    <div class="col-md-4">
                        <label for="middle_name" class="form-label fw-semibold">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control shadow-sm">
                    </div>
                    <div class="col-md-4">
                        <label for="last_name" class="form-label fw-semibold">Last Name<span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control shadow-sm" required>
                    </div>
                </div>

                {{-- Barangay --}}
                <div class="mt-3">
                    @if(isset($barangay))
                        <label class="form-label fw-semibold">Barangay</label>
                        <input type="text" class="form-control shadow-sm" value="{{ $barangay->barangay_name }}" readonly>
                        <input type="hidden" name="barangay_id" value="{{ $barangay->id }}">
                    @elseif(isset($barangays) && $barangays->count() === 1)
                        <label class="form-label fw-semibold">Barangay</label>
                        <input type="text" class="form-control shadow-sm" value="{{ $barangays->first()->barangay_name }}" readonly>
                        <input type="hidden" name="barangay_id" value="{{ $barangays->first()->id }}">
                    @else
                        <label for="barangay_id" class="form-label fw-semibold">Barangay</label>
                        <select name="barangay_id" id="barangay_id_select" class="form-select shadow-sm" required>
                            <option value="">-- Select Barangay --</option>
                            @foreach($barangays ?? [] as $b)
                                <option value="{{ $b->id }}">{{ $b->barangay_name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                {{-- Gender & Disability --}}
                <div class="row mt-3 g-3">
                    <div class="col-md-6">
                        <label for="gender" class="form-label fw-semibold">Gender<span class="text-danger">*</span></label>
                        <select name="gender" class="form-select shadow-sm" required>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="type_of_disability" class="form-label fw-semibold">Type of Disability<span class="text-danger">*</span></label>

                        {{-- existing options select (optional) --}}
                        <select id="disabilitySelect" class="form-select mb-2">
                            <option value="">-- Select existing (optional) --</option>
                            @foreach(($allDisabilities ?? []) as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>

                        {{-- editable input (required) --}}
                        <input type="text" name="type_of_disability" id="type_of_disability" class="form-control shadow-sm" required>
                    </div>
                </div>

                {{-- PWD ID & Remarks --}}
                <div class="row mt-3 g-3">
                    <div class="col-md-6">
                        <label for="pwd_id_number" class="form-label fw-semibold">PWD ID Number<span class="text-danger">*</span></label>
                        <input type="text" name="pwd_id_number" class="form-control shadow-sm" required>
                    </div>
                    <div class="col-md-6">
                        <label for="remarks" class="form-label fw-semibold">Remarks</label>

                        {{-- existing remarks select --}}
                        <select id="remarksSelect" class="form-select mb-2">
                            <option value="">-- Select existing (optional) --</option>
                            @foreach(($allRemarks ?? []) as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>

                        {{-- editable remarks input --}}
                        <input type="text" name="remarks" id="remarks" class="form-control shadow-sm">
                    </div>
                </div>

                {{-- Birthday / Age --}}
                <div class="row mt-3 g-3">
                    <div class="col-md-6">
                        <label for="birthday" class="form-label fw-semibold">Birthday</label>
                        {{-- Birthday now required for manual add; prevent selecting future dates --}}
                        <input type="date" name="birthday" id="birthday" class="form-control shadow-sm" required max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="age" class="form-label fw-semibold">Age</label>
                        <input type="number" name="age" id="age" class="form-control shadow-sm" readonly>
                    </div>
                </div>

                {{-- Validity (Manual Input) --}}
                <div class="row mt-3 g-3">
                    <div class="col-md-6">
                        <label for="validity_years_input" class="form-label fw-semibold">Years of Validity <span class="text-danger">*</span></label>
                        <input type="number" id="validity_years_input" name="validity_years" class="form-control shadow-sm" min="1" max="10" value="5" required>
                        <small class="text-muted">Set validity years for manually entered beneficiary.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Estimated Expiration</label>
                        <input type="text" id="valid_to_preview_input" class="form-control shadow-sm bg-light" readonly>
                    </div>
                </div>

                {{-- ✅ Save Button --}}
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i>Save Beneficiary
                    </button>
                </div>
            </form>

            {{-- ✅ CSV Import Section --}}
            <hr class="my-4">
            <div class="mb-3">
                <h5 class="fw-bold text-secondary">
                    <i class="bi bi-upload me-2"></i>Import PWD List (CSV)
                </h5>

                {{-- ★ DOWNLOAD TEMPLATE BUTTON for PWD --}}
                <a href="{{ route('pwd.download-template') }}" class="btn btn-info mb-3">
                    <i class="fa fa-download me-1"></i> Download CSV Template
                </a>

                <form method="POST" action="{{ route('pwd.importCsv') }}" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <input type="hidden" name="encrypted_barangay_id" id="encrypted_barangay_id"
                        value="{{ isset($barangay) ? Crypt::encrypt($barangay->id) : (isset($barangays) && $barangays->count() === 1 ? Crypt::encrypt($barangays->first()->id) : '') }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="validity_years_import" class="form-label fw-semibold">Years of Validity</label>
                            <input type="number" name="validity_years_import" id="validity_years_import" class="form-control shadow-sm" min="1" max="10" value="5" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estimated Expiration</label>
                            <input type="text" id="valid_to_preview_import" class="form-control shadow-sm bg-light" readonly>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="csv_file" class="form-label fw-semibold">CSV File</label>
                        <input type="file" name="csv_file" id="csv_file"
                            class="form-control shadow-sm" accept=".csv" required>
                    </div>

                    <button type="submit" class="btn btn-success mt-3">
                        <i class="bi bi-file-earmark-arrow-up me-2"></i>Import CSV
                    </button>

                    {{-- Progress UI --}}
                    <div id="progressContainer" class="mt-3" style="display:none;">
                        <div class="progress" style="height:8px;">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%"></div>
                        </div>
                        <p id="progressMessage" class="small mt-2"></p>
                    </div>

                    <!-- Undo button (hidden until a successful import returns IDs) -->
                    <div class="mt-2">
                        <button type="button" id="undoImportBtn" class="btn btn-warning btn-sm" style="display:none;">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Undo Last Import
                        </button>
                    </div>
                </form>
            </div>

            <!-- Modern Undo Modal -->
            <div class="modal fade" id="undoImportModal" tabindex="-1" aria-labelledby="undoImportModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="undoImportModalLabel">Undo Last Import</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p id="undoImportModalBody">Are you sure you want to undo the last import?</p>
                            <p class="small text-muted">This will delete the most recently imported beneficiary records. This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="confirmUndoBtn" class="btn btn-warning">
                                <span id="confirmUndoBtnLabel"><i class="bi bi-arrow-counterclockwise me-1"></i>Undo Import</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/add-pwdbeneficiaries.js') }}"></script>
@endpush
@endsection
