@extends('layouts.adminlayout')

@section('title', 'Registered Beneficiaries')

@section('content')
<div class="container py-4">

    <a href="{{ route('admin.documents.selector', ['barangay_id' => request('barangay_id')]) }}" class="btn btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back
    </a>

    <h2 class="mb-4">
        Registered Beneficiaries for Barangay:
        <span class="text-primary">{{ $barangay->barangay_name }}</span>
    </h2>

    <h5 class="mb-3">
        Beneficiary Type:
        <span class="fw-bold">{{ ucfirst($beneficiaryType) }}</span>
    </h5>

    @php
        $filteredBeneficiaries = $beneficiaries->filter(function($b) use ($beneficiaryType) {
            return stripos($b->beneficiary_type, $beneficiaryType) !== false;
        });
    @endphp

    @if($filteredBeneficiaries->isEmpty())
        <div class="alert alert-warning">
            No registered beneficiaries found for this barangay and beneficiary type.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>View Files</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($filteredBeneficiaries as $index => $b)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $b->last_name }}, {{ $b->first_name }}</td>
                            <td>{{ $b->beneficiary_type }}</td>
                            <td>{{ $b->email ?? 'N/A' }}</td>
                            <td>{{ $b->phone ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('document.beneficiary.program.documents', [
                                    'barangay_id' => encrypt($barangay->id),
                                    'beneficiary_id' => encrypt($b->id)
                                ]) }}" class="btn btn-outline-primary btn-sm">
                                    View Files
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
@endsection
