@extends('layouts.adminlayout')

@section('title', 'Add Senior Citizen Beneficiary')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Add Senior Citizen Beneficiary</h1>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Add Beneficiary Form --}}
    <form method="POST" action="{{ route('beneficiaries.store') }}" id="addBeneficiaryForm">
        @csrf
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name') }}" required>
            </div>
            <div class="col-md-4">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name') }}" required>
            </div>
            <div class="col-md-4">
                <label for="middle_name" class="form-label">Middle Name</label>
                <input type="text" name="middle_name" id="middle_name" class="form-control" value="{{ old('middle_name') }}">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="birthday" class="form-label">Birthday</label>
                <input type="date" name="birthday" id="birthday" class="form-control" value="{{ old('birthday') }}" required>
            </div>
            <div class="col-md-2">
                <label for="age" class="form-label">Age</label>
                <input type="number" name="age" id="age" class="form-control" value="{{ old('age') }}" readonly required>
            </div>
            <div class="col-md-2">
                <label for="gender" class="form-label">Gender</label>
                <select name="gender" id="gender" class="form-select" required>
                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="civil_status" class="form-label">Civil Status</label>
                <input type="text" name="civil_status" id="civil_status" class="form-control" value="{{ old('civil_status') }}" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="osca_number" class="form-label">OSCA Number</label>
                <input type="text" name="osca_number" id="osca_number" class="form-control" value="{{ old('osca_number') }}" required>
            </div>
            <div class="col-md-4">
                <label for="date_issued" class="form-label">Date Issued</label>
                <input type="date" name="date_issued" id="date_issued" class="form-control" value="{{ old('date_issued') }}" required>
            </div>
            <div class="col-md-4">
                <label for="remarks" class="form-label">Remarks</label>
                <input type="text" name="remarks" id="remarks" class="form-control" value="{{ old('remarks') }}">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="national_id" class="form-label">National ID</label>
                <input type="text" name="national_id" id="national_id" class="form-control" value="{{ old('national_id') }}">
            </div>
            <div class="col-md-4">
                <label for="pkn" class="form-label">PKN</label>
                <input type="text" name="pkn" id="pkn" class="form-control" value="{{ old('pkn') }}">
            </div>
            <div class="col-md-4">
                <label for="rrn" class="form-label">RRN</label>
                <input type="text" name="rrn" id="rrn" class="form-control" value="{{ old('rrn') }}">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="barangay_id" class="form-label">Barangay</label>
                <input type="text" name="barangay_name" id="barangay_name" class="form-control" value="{{ $barangay->barangay_name }}" readonly>
                <input type="hidden" name="barangay_id" value="{{ encrypt($barangay->id) }}">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Beneficiary</button>
    </form>

    {{-- Import CSV --}}
    <h2 class="mt-5">Import Beneficiaries</h2>
    <form id="importForm" method="POST" enctype="multipart/form-data" action="{{ route('beneficiaries.import') }}">
        @csrf
        <input type="hidden" name="encrypted_barangay_id" value="{{ encrypt($barangay->id) }}">
        <div class="mb-3">
            <label for="csv_file" class="form-label">Upload CSV File</label>
            <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
        </div>
        <button type="submit" class="btn btn-success">Import CSV</button>
    </form>

    {{-- Progress UI --}}
    <div id="progressContainer" class="mt-4" style="display: none;">
        <div class="progress">
            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
        <p id="progressMessage" class="mt-2"></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const importRoute = "{{ route('beneficiaries.import') }}";
</script>
<script src="{{ asset('js/add-beneficiaries.js') }}"></script>
@endpush
