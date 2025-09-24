@extends('layouts.adminlayout')

@section('title', 'Senior Citizen Beneficiaries')

@section('content')
<link rel="stylesheet" href="{{ asset('css/senior-citizen-interface.css') }}">

<div class="container py-4">
    <h1 class="mb-4">Senior Citizen Beneficiaries</h1>

    <!-- Search Bar -->
    <div class="search-bar">
        <input type="text" id="searchBarangay" class="form-control" placeholder="Search Barangays...">
    </div>

    <!-- View Toggle -->
    <div class="view-toggle">
        <button class="btn btn-secondary" id="toggleView"><i class="fa fa-th"></i> Toggle View</button>
    </div>

    <!-- Barangay Cards -->
    <div class="card-container" id="cardView">
        @forelse ($barangays as $barangay)
            <div class="barangay-card">
                <h5>{{ $barangay->barangay_name }}</h5>
                <p>View beneficiaries for this barangay.</p>
                <a href="{{ route('senior-citizen.view', ['barangay' => encrypt($barangay->id)]) }}">View Beneficiaries</a>
            </div>
        @empty
            <p class="text-muted">No barangays found.</p>
        @endforelse
    </div>

    <!-- Barangay List -->
    <div class="list-container" id="listView">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Barangay Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($barangays as $index => $barangay)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $barangay->barangay_name }}</td>
                        <td>
                            <a href="{{ route('senior-citizen.view', ['barangay' => encrypt($barangay->id)]) }}" class="btn btn-primary btn-sm">View Beneficiaries</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-muted">No barangays found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pass the route URL to JavaScript -->
<script>
    const searchRoute = "{{ route('senior-citizen.search') }}";
</script>

<script src="{{ asset('js/senior-citizen-interface.js') }}"></script>
@endsection
