@extends('layouts.adminlayout')

@section('title', 'Barangay Representatives')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-semibold mb-0">Barangay Representatives</h2>
        <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-lg rounded-4">
        <div class="card-header" style="background: #22c55e; color: #fff; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
            <h5 class="mb-0 fw-semibold">
                <i class="fa fa-handshake me-2"></i> Barangay Representatives
            </h5>
        </div>
        <div class="card-body" style="background: #dcfce7;">
            @if($brgyReps->isNotEmpty())
                <div class="table-responsive">
                    <table class="table align-middle table-hover table-borderless" style="background: #dcfce7;">
                        <thead>
                            <tr style="background: linear-gradient(90deg, #22c55e 70%, #16a34a 100%); color: #fff;">
                                <th scope="col" class="rounded-start">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Contact</th>
                                <th scope="col">Barangay</th>
                                <th scope="col">Profile Picture</th>
                                <th scope="col" class="text-end rounded-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($brgyReps as $index => $rep)
                                <tr class="bg-white shadow-sm rounded-3 mb-2" style="vertical-align: middle;">
                                    <td class="fw-bold text-success">{{ $index + 1 }}</td>
                                    <td class="fw-medium">{{ $rep->full_name }}</td>
                                    <td>
                                        @if($rep->email)
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">{{ $rep->email }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">{{ $rep->contact }}</span>
                                    </td>
                                    <td>
                                        @if($rep->barangay)
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">{{ $rep->barangay->barangay_name }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($rep->profile_picture)
                                            <img src="{{ asset('storage/' . $rep->profile_picture) }}"
                                                 alt="Profile Picture"
                                                 class="rounded-circle border border-success"
                                                 style="width: 48px; height: 48px; object-fit: cover;">
                                        @else
                                            <span class="text-muted">No Picture</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('members.show', Crypt::encrypt($rep->id)) }}"
                                           class="btn btn-sm btn-outline-success rounded-pill px-3">
                                            <i class="fa fa-eye me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted py-5">
                    <i class="fa fa-info-circle fa-2x mb-3 d-block"></i>
                    <p class="mb-0">No Barangay Representatives found.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
