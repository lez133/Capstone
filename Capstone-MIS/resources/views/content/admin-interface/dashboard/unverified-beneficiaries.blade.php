@extends('layouts.adminlayout')

@section('title', 'Unverified Beneficiaries')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 fw-bold text-center">Unverified Beneficiaries</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-danger">
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Type</th>
                    <th>Barangay</th>
                    <th>Gender</th>
                    <th>Birthday</th>
                </tr>
            </thead>
            <tbody>
                @forelse($unverifiedBeneficiaries as $index => $b)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $b->last_name }}, {{ $b->first_name }} {{ $b->middle_name ?? '' }}</td>
                        <td>{{ $b->beneficiary_type }}</td>
                        <td>{{ $b->barangay->barangay_name ?? 'N/A' }}</td>
                        <td>
                            @if(strtoupper($b->gender) === 'M')
                                Male
                            @elseif(strtoupper($b->gender) === 'F')
                                Female
                            @else
                                {{ $b->gender }}
                            @endif
                        </td>
                        <td>{{ $b->birthday ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No unverified beneficiaries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
