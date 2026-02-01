@extends('layouts.adminlayout')

@section('title', 'MSWD Members')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-semibold mb-0">MSWD Members</h2>
        <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-lg rounded-4">
        <div class="card-header" style="background: #2563eb; color: #fff; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
            <h5 class="mb-0 fw-semibold">
                <i class="fa fa-users me-2"></i> Member Directory
            </h5>
        </div>
        <div class="card-body" style="background: #e0e7ff;">
            @if($mswdMembers->isNotEmpty())
                <div class="table-responsive">
                    <table class="table align-middle table-hover table-borderless" style="background: #e0e7ff;">
                        <thead>
                            <tr style="background: linear-gradient(90deg, #2563eb 70%, #1e40af 100%); color: #fff;">
                                <th scope="col" class="rounded-start">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Contact</th>
                                <th scope="col">Profile Picture</th>
                                <th scope="col" class="text-end rounded-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mswdMembers as $index => $member)
                                <tr class="bg-white shadow-sm rounded-3 mb-2" style="vertical-align: middle;">
                                    <td class="fw-bold text-primary">{{ $index + 1 }}</td>
                                    <td class="fw-medium">{{ $member->full_name }}</td>
                                    <td>
                                        @if($member->email)
                                            <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">{{ $member->email }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">{{ $member->contact }}</span>
                                    </td>
                                    <td>
                                        @if($member->profile_picture)
                                            <img src="{{ asset('storage/' . $member->profile_picture) }}"
                                                 alt="Profile Picture"
                                                 class="rounded-circle border border-primary"
                                                 style="width: 48px; height: 48px; object-fit: cover;">
                                        @else
                                            <span class="text-muted">No Picture</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('members.show', Crypt::encrypt($member->id)) }}"
                                           class="btn btn-sm btn-outline-primary rounded-pill px-3">
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
                    <p class="mb-0">No MSWD Members found.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
