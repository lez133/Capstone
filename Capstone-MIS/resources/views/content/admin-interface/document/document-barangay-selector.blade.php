{{-- filepath: c:\Lara\Capstone-MIS\resources\views\content\admin-interface\document\document-barangay-selector.blade.php --}}
@extends('layouts.adminlayout')

@section('title', 'Select Barangay for Document')

@section('content')
<link rel="stylesheet" href="{{ asset('css/brgyselection-interface.css') }}">

@php
    // normalize items whether $barangays contains arrays or model objects
    $initialBarangays = $barangays->map(function ($b) {
        return [
            'id' => is_array($b) ? ($b['id'] ?? null) : ($b->id ?? null),
            'barangay_name' => is_array($b) ? ($b['barangay_name'] ?? '') : ($b->barangay_name ?? ''),
            'encrypted_id' => is_array($b) ? ($b['encrypted_id'] ?? '') : ($b->encrypted_id ?? ''),
        ];
    })->values();
@endphp

<div class="container py-4">
    <h1 class="mb-4">Select Barangay to View Document</h1>

    <!-- Search Bar -->
    <div class="search-bar mt-3 mb-3">
        <input type="text" id="searchBarangay" class="form-control" placeholder="Search Barangays...">
    </div>

    <!-- View Toggle -->
    <div class="view-toggle mb-3">
        <button class="btn btn-secondary" id="toggleView"><i class="fa fa-th"></i> Toggle View</button>
    </div>

    <!-- Barangay Cards -->
    <div class="card-container" id="cardView" data-barangays='@json($initialBarangays)'>
        @forelse ($initialBarangays as $barangay)
            <div class="barangay-card">
                <h5>{{ $barangay['barangay_name'] }}</h5>
                <p>View beneficiaries for this barangay.</p>
                <button
                    class="btn btn-primary btn-sm select-brgy-btn"
                    data-id="{{ $barangay['id'] }}"
                    data-encrypted-id="{{ $barangay['encrypted_id'] }}"
                >
                    Select Barangay
                </button>
            </div>
        @empty
            <p class="text-muted">No barangays found.</p>
        @endforelse
    </div>

    <!-- Barangay List -->
    <div class="list-container" id="listView" style="display:none;">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Barangay Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($initialBarangays as $index => $barangay)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $barangay['barangay_name'] }}</td>
                        <td>
                            <button
                                class="btn btn-primary btn-sm select-brgy-btn"
                                data-id="{{ $barangay['id'] }}"
                            >
                                Select Barangay
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-muted text-center">No barangays found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    const selectorRoute = "{{ route('admin.documents.selector') }}";
    const initialBarangays = JSON.parse(document.getElementById('cardView').dataset.barangays || '[]');
</script>

<!-- include the JS that drives toggle/search -->
<script src="{{ asset('js/brgydocumentselector.js') }}"></script>
@endsection
