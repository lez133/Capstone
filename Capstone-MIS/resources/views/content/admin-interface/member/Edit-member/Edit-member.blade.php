@extends('layouts.adminlayout')

@section('title', 'Edit Member')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0">Edit Member</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('members.update', $member->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="fname" class="form-label">First Name</label>
                        <input type="text" name="fname" id="fname" class="form-control" value="{{ $member->fname }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="lname" class="form-label">Last Name</label>
                        <input type="text" name="lname" id="lname" class="form-control" value="{{ $member->lname }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ $member->email }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="contact" class="form-label">Contact Number</label>
                        <input type="text" name="contact" id="contact" class="form-control" value="{{ $member->contact }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="role" class="form-label">Role</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="MSWD Representative" {{ $member->role == 'MSWD Representative' ? 'selected' : '' }}>MSWD Representative</option>
                            <option value="Barangay Representative" {{ $member->role == 'Barangay Representative' ? 'selected' : '' }}>Barangay Representative</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="profile_picture" class="form-label">Profile Picture</label>
                        <input type="file" name="profile_picture" id="profile_picture" class="form-control">
                        @if($member->profile_picture)
                            <small class="text-muted">Current Picture: <a href="{{ asset('storage/' . $member->profile_picture) }}" target="_blank">View</a></small>
                        @endif
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Update Member</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
