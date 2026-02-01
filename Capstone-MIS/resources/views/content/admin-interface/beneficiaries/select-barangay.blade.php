@extends('layouts.adminlayout')

@section('title', 'Select Barangay')

@section('content')
<link rel="stylesheet" href="{{ asset('css/brgyselection-interface.css') }}">

<div class="container py-4">
    <h1 class="mb-4">Select Barangay to View Beneficiaries</h1>
    <!-- Search Bar -->
    <div class="search-bar mt-3 mb-3">
        <input type="text" id="searchBarangay" class="form-control" placeholder="Search Barangays...">
    </div>

    <!-- View Toggle -->
    <div class="view-toggle mb-3">
        <button class="btn btn-secondary" id="toggleView"><i class="fa fa-th"></i> Toggle View</button>
    </div>

    <!-- Barangay Cards -->
    <div class="card-container" id="cardView">
        @forelse ($barangays as $barangay)
            <div class="barangay-card">
                <h5>{{ $barangay->barangay_name }}</h5>

                <!-- Summary badges -->
                <div class="mb-2">
                    <span class="badge bg-primary me-1">Seniors (specialized): {{ $barangay->counts['total_senior_registered'] ?? 0 }}</span>
                    <span class="badge bg-success me-1">PWDs (specialized): {{ $barangay->counts['total_pwd_registered'] ?? 0 }}</span>
                </div>

                <!-- Verified vs Unverified breakdown -->
                <div class="small text-muted mb-3">
                    <div>Senior verified: {{ $barangay->counts['verified_senior'] ?? 0 }} |
                        Unverified: {{ $barangay->counts['unverified_senior'] ?? 0 }} (Total in main: {{ $barangay->counts['total_senior'] ?? 0 }})</div>
                    <div>PWD verified: {{ $barangay->counts['verified_pwd'] ?? 0 }} |
                        Unverified: {{ $barangay->counts['unverified_pwd'] ?? 0 }} (Total in main: {{ $barangay->counts['total_pwd'] ?? 0 }})</div>
                </div>

                <p>View beneficiaries for this barangay.</p>
                <a href="{{ route('beneficiaries.interface', ['encryptedBarangayId' => encrypt($barangay->id)]) }}"
                   class="btn btn-primary btn-sm">
                    View Beneficiaries
                </a>
            </div>
        @empty
            <p class="text-muted">No barangays found.</p>
        @endforelse
    </div>

    <!-- Barangay List -->
    <div class="list-container" id="listView">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Barangay Name</th>
                    <th>Totals (S/PWD)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($barangays as $index => $barangay)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $barangay->barangay_name }}</td>
                        <td>
                            <div>
                                <small>Seniors total: {{ $barangay->counts['total_senior_registered'] ?? 0 }} |
                                    Registered: {{ $barangay->counts['registered_senior'] ?? ($barangay->counts['flagged_senior'] ?? 0) }}</small><br>
                                <small>PWDs total: {{ $barangay->counts['total_pwd_registered'] ?? 0 }} |
                                    Registered: {{ $barangay->counts['registered_pwd'] ?? ($barangay->counts['flagged_pwd'] ?? 0) }}</small>
                            </div>
                            <div class="mt-1 small text-muted">
                                <small>Sr unreg: {{ $barangay->counts['unregistered_senior'] ?? 0 }}</small><br>
                                <small>PWD unreg: {{ $barangay->counts['unregistered_pwd'] ?? 0 }}</small>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('beneficiaries.interface', ['encryptedBarangayId' => encrypt($barangay->id)]) }}"
                               class="btn btn-primary btn-sm">
                                View Beneficiaries
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-muted text-center">No barangays found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- JS Routes -->
<script>
    const searchRoute = "{{ route('barangay.search') }}";
    const viewRoute = "{{ route('beneficiaries.interface', ['encryptedBarangayId' => '__ID__']) }}";
</script>

@php
    $initialBarangays = $barangays->map(function ($b) {
        return [
            'barangay_name' => $b->barangay_name,
            'encrypted_id'  => encrypt($b->id),
            'counts'        => $b->counts,
            'id'            => $b->id,
        ];
    })->values();

    // encode safely for embedding
    $initialJson = $initialBarangays->toJson(JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $initialBase64 = base64_encode($initialJson);
@endphp

<script>
    // decode and parse in browser (safe from quote/linebreak issues)
    const initialBarangays = JSON.parse(atob("{{ $initialBase64 }}"));
</script>

<script src="{{ asset('js/brgyselection-interface.js') }}"></script>

@endsection
