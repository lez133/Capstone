@extends('layouts.adminlayout')

@section('title', 'Not Verified Beneficiaries')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Not Verified Beneficiaries</h1>

    <div class="mt-4">
        <a href="{{ route('senior-citizens.manage', $encryptedBarangayId) }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Manage Beneficiaries
        </a>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('senior-citizens.not-verified', $encryptedBarangayId) }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by name, OSCA number, or email" value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">
                <i class="fa fa-search"></i> Search
            </button>
        </div>
    </form>

    @if ($notVerifiedBeneficiaries->isEmpty())
        <p class="text-muted">No not verified beneficiaries found.</p>
    @else
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th> <!-- Add this line -->
                    <th>Age</th>
                    <th>Gender</th>
                    <th>OSCA Number</th>
                    <th>Civil Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($notVerifiedBeneficiaries as $index => $beneficiary)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $beneficiary->last_name }}, {{ $beneficiary->first_name }}</td>
                        <td>{{ $beneficiary->email }}</td> <!-- Add this line -->
                        <td>{{ $beneficiary->age }}</td>
                        <td>{{ $beneficiary->gender }}</td>
                        <td>{{ $beneficiary->osca_number }}</td>
                        <td>{{ $beneficiary->civil_status }}</td>
                        <td>
                            <!-- Verify -->
                            <form method="POST" action="{{ route('senior-citizens.verify', $beneficiary->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Verify</button>
                            </form>

                            <!-- Edit -->
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $beneficiary->id }}">Edit</button>

                            <!-- Delete -->
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $beneficiary->id }}">Delete</button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal{{ $beneficiary->id }}" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('senior-citizens.edit', $beneficiary->id) }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel">Edit Beneficiary</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="last_name" class="form-label">Last Name</label>
                                            <input type="text" name="last_name" class="form-control" value="{{ $beneficiary->last_name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="first_name" class="form-label">First Name</label>
                                            <input type="text" name="first_name" class="form-control" value="{{ $beneficiary->first_name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="osca_number" class="form-label">OSCA Number</label>
                                            <input type="text" name="osca_number" class="form-control" value="{{ $beneficiary->osca_number }}" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal{{ $beneficiary->id }}" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('senior-citizens.delete', $beneficiary->id) }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel">Delete Beneficiary</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete this beneficiary?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
