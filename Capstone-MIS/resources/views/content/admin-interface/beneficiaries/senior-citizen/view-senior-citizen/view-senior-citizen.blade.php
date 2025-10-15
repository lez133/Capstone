@extends('layouts.adminlayout')

@section('title', 'Senior Citizen Beneficiaries - ' . $barangay->barangay_name)

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Senior Citizen Beneficiaries</h1>
    <h3 class="mb-4">Barangay: {{ $barangay->barangay_name }}</h3>
    <div class="mt-4">
        <a href="{{ route('senior-citizen.interface') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Barangays
        </a>
    </div>
    <!-- Search Bar -->
    <form method="GET" action="{{ route('senior-citizen-beneficiaries.view', ['encryptedBarangayId' => Crypt::encrypt($barangay->id)]) }}" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search beneficiaries..." value="{{ $search ?? '' }}">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-search"></i> Search
            </button>
        </div>
    </form>

    <!-- Add Beneficiary Button -->
    <div class="mb-4">
        <a href="{{ route('senior-citizen-beneficiaries.create', ['barangay' => encrypt($barangay->id)]) }}" class="btn btn-primary">
            <i class="fa fa-user-plus"></i> Add Senior Citizen Beneficiary
        </a>
    </div>

    @if ($beneficiaries->count() > 0)
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fa fa-users"></i> Beneficiaries List
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Birthday</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Civil Status</th>
                            <th>OSCA Number</th>
                            <th>Date Issued</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($beneficiaries as $index => $beneficiary)
                            <tr>
                                <td>{{ $loop->iteration + ($beneficiaries->currentPage() - 1) * $beneficiaries->perPage() }}</td>
                                <td>{{ $beneficiary->last_name }}</td>
                                <td>{{ $beneficiary->first_name }}</td>
                                <td>{{ $beneficiary->middle_name }}</td>
                                <td>{{ $beneficiary->birthday }}</td>
                                <td>{{ $beneficiary->age }}</td>
                                <td>{{ $beneficiary->gender }}</td>
                                <td>{{ $beneficiary->civil_status }}</td>
                                <td>{{ Crypt::decrypt($beneficiary->osca_number) }}</td>
                                <td>{{ $beneficiary->date_issued }}</td>
                                <td>{{ $beneficiary->remarks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Links -->
        <div class="mt-4 d-flex justify-content-center">
            @if ($beneficiaries->hasPages())
                <nav>
                    <ul class="pagination">
                        <!-- Previous Page Link -->
                        @if ($beneficiaries->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $beneficiaries->previousPageUrl() }}" rel="prev">Previous</a>
                            </li>
                        @endif

                        <!-- Pagination Elements -->
                        @foreach ($beneficiaries->links()->elements[0] as $page => $url)
                            @if ($page == $beneficiaries->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        <!-- Next Page Link -->
                        @if ($beneficiaries->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $beneficiaries->nextPageUrl() }}" rel="next">Next</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">Next</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    @else
        <p class="text-muted">No beneficiaries found for this barangay.</p>
    @endif

</div>
@endsection
