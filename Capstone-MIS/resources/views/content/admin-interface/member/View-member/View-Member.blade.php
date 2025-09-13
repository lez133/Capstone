@extends('layouts.adminlayout')

@section('title', 'View Member')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Member Details</h5>
            <a href="{{ route('members.index') }}" class="btn btn-light btn-sm">Back to Members</a>
        </div>
        <div class="card-body">
            <div class="row">

                <div class="col-md-4 text-center">
                    @if($member->profile_picture)
                        <img src="{{ asset('storage/' . $member->profile_picture) }}" alt="Profile Picture" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px;">
                            <span class="fw-bold">No Image</span>
                        </div>
                    @endif
                    <h6 class="fw-bold">{{ $member->full_name }}</h6>
                    <p class="text-muted mb-0">{{ $member->role }}</p>
                </div>

                <!-- Member Information -->
                <div class="col-md-8">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Personal Information</h6>
                            <p class="mb-1"><strong>First Name:</strong> {{ $member->fname }}</p>
                            <p class="mb-1"><strong>Middle Name:</strong> {{ $member->mname ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Last Name:</strong> {{ $member->lname }}</p>
                            <p class="mb-1"><strong>Gender:</strong> {{ $member->gender }}</p>
                            <p class="mb-1"><strong>Birthday:</strong> {{ \Carbon\Carbon::parse($member->birthday)->format('F d, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Contact Information</h6>
                            <p class="mb-1"><strong>Email:</strong> {{ $member->email }}</p>
                            <p class="mb-1"><strong>Contact Number:</strong> {{ $member->contact }}</p>
                            <p class="mb-1"><strong>Username:</strong> {{ $member->username }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Account Details</h6>
                            <p class="mb-1"><strong>Created By:</strong> {{ $member->creator ? $member->creator->name : 'N/A' }}</p>
                            <p class="mb-1"><strong>Created At:</strong> {{ $member->created_at->format('F d, Y h:i A') }}</p>
                            <p class="mb-1"><strong>Updated At:</strong> {{ $member->updated_at->format('F d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('members.edit', Crypt::encrypt($member->id)) }}" class="btn btn-warning btn-sm">Edit Member</a>
            <form action="#" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this member?')">Delete Member</button>
            </form>
        </div>
    </div>
</div>
@endsection
