@extends('layouts.adminlayout')

@section('title', 'Select Barangay - Distribution')

@section('content')
<link rel="stylesheet" href="{{ asset('css/brgyselection-interface.css') }}">

<div class="container py-4">
    <h1 class="mb-4">Select Barangay to View Distribution</h1>

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

                <div class="mb-2">
                    <span class="badge bg-info me-1">Upcoming: {{ $barangay->distribution_counts['upcoming'] ?? 0 }}</span>
                    <span class="badge bg-warning text-dark me-1">Ongoing: {{ $barangay->distribution_counts['ongoing'] ?? 0 }}</span>
                    <span class="badge bg-secondary me-1">Completed: {{ $barangay->distribution_counts['completed'] ?? 0 }}</span>
                    <span class="badge bg-primary">Total: {{ $barangay->distribution_counts['total'] ?? 0 }}</span>
                </div>

                <p>View schedules for this barangay.</p>
                {{-- Link to category, carry the selected barangay (encrypted) as query param --}}
                <a href="{{ route('distribution.category') }}?barangay_id={{ urlencode(encrypt($barangay->id)) }}"
                   class="btn btn-primary btn-sm">
                    Select Barangay
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
                    <th>Programs (U/O/C)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($barangays as $index => $barangay)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $barangay->barangay_name }}</td>
                        <td>
                            <small>
                                U: {{ $barangay->distribution_counts['upcoming'] ?? 0 }} /
                                O: {{ $barangay->distribution_counts['ongoing'] ?? 0 }} /
                                C: {{ $barangay->distribution_counts['completed'] ?? 0 }}
                            </small>
                        </td>
                        <td>
                            <a href="{{ route('distribution.schedules', ['status' => 'Upcoming']) }}?barangay_id={{ urlencode(encrypt($barangay->id)) }}"
                               class="btn btn-primary btn-sm">
                                View Schedules for Barangay
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
    const selectorRoute = "{{ route('distribution.category') }}"; // use this in JS to build program links
    // remove/ignore viewRoute here to avoid accidental reuse
</script>

@php
    $initialBarangays = $barangays->map(function($b) {
        return [
            'barangay_name' => $b->barangay_name,
            'encrypted_id' => encrypt($b->id),
            'distribution_counts' => $b->distribution_counts,
        ];
    })->values();

    $initialJson = $initialBarangays->toJson(JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $initialBase64 = base64_encode($initialJson);
@endphp

{{-- expose initial data for client search safely --}}
<script>
    const initialBarangays = JSON.parse(atob("{{ $initialBase64 }}"));
</script>

<script src="{{ asset('js/brgydistributionselector.js') }}"></script>

@endsection
