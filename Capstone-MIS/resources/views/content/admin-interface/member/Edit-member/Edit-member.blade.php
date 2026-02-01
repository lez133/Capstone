@extends('layouts.adminlayout')

@section('title', 'Edit Member')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container mt-4">
    <div class="mb-3">
        <a href="{{ route('members.index') }}" class="btn btn-outline-primary rounded-pill px-4">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-gradient-warning text-white rounded-top-4"
             style="background: linear-gradient(90deg, #ffa726 0%, #ffb74d 100%);">
            <h5 class="mb-0 fw-semibold"><i class="fa fa-edit me-2"></i>Edit Member</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('members.update', Crypt::encrypt($member->id)) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="fname" class="form-label fw-bold text-secondary">First Name</label>
                        <input type="text" name="fname" id="fname" class="form-control rounded-pill" value="{{ $member->fname }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="lname" class="form-label fw-bold text-secondary">Last Name</label>
                        <input type="text" name="lname" id="lname" class="form-control rounded-pill" value="{{ $member->lname }}" required>
                    </div>
                </div>

                <hr class="my-3 border border-warning border-2 opacity-50">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-bold text-secondary">Email</label>
                        <input type="email" name="email" id="email" class="form-control rounded-pill" value="{{ $member->email }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="contact" class="form-label fw-bold text-secondary">Contact Number</label>
                        <input type="text" name="contact" id="contact" class="form-control rounded-pill" value="{{ $member->contact }}" required>
                    </div>
                </div>

                <hr class="my-3 border border-warning border-2 opacity-50">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="role" class="form-label fw-bold text-secondary">Role</label>
                        <select name="role" id="role" class="form-select rounded-pill" required>
                            <option value="MSWD Representative" {{ $member->role == 'MSWD Representative' ? 'selected' : '' }}>MSWD Representative</option>
                            <option value="Barangay Representative" {{ $member->role == 'Barangay Representative' ? 'selected' : '' }}>Barangay Representative</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="profile_picture" class="form-label fw-bold text-secondary">Profile Picture</label>
                        <input type="file" name="profile_picture" id="profile_picture" class="form-control rounded-pill">
                        @if($member->profile_picture)
                            <small class="text-muted">Current Picture: <a href="{{ asset('storage/' . $member->profile_picture) }}" target="_blank">View</a></small>
                        @endif
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">
                        <i class="fa fa-save me-1"></i>Update Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@if($errors->any())
    <div class="alert alert-danger rounded-4 shadow-sm mt-3">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
