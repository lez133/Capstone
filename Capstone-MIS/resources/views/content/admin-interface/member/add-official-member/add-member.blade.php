@extends('layouts.adminlayout')

@section('title', 'Add Member')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add Member</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('members.store') }}" method="POST" enctype="multipart/form-data" id="addMemberForm">
                @csrf

                <!-- Step Navigation -->
                <ul class="nav nav-pills mb-4" id="step-nav">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" id="step1-tab" data-bs-toggle="pill" data-bs-target="#step1">Personal Information</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" id="step2-tab" data-bs-toggle="pill" data-bs-target="#step2" disabled>Contact Information</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" id="step3-tab" data-bs-toggle="pill" data-bs-target="#step3" disabled>Role and Status</button>
                    </li>
                </ul>

                <!-- Step Content -->
                <div class="tab-content">
                    <!-- Step 1 -->
                    <div class="tab-pane fade show active" id="step1">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="fname" id="fname" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="mname" id="mname" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="lname" id="lname" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Day *</label>
                                <input type="number" name="birth_day" id="birth_day" class="form-control" min="1" max="31" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Month *</label>
                                <input type="number" name="birth_month" id="birth_month" class="form-control" min="1" max="12" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Year *</label>
                                <input type="number" name="birth_year" id="birth_year" class="form-control" min="1900" max="{{ date('Y') }}" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Gender *</label>
                                <select name="gender" id="gender" class="form-select" required>
                                    <option value="" disabled selected>Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" name="profile_picture" id="profile_picture" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary" id="next-step1">Next</button>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="tab-pane fade" id="step2">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Contact Number *</label>
                                <input type="number" name="contact" id="contact" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" id="prev-step2">Previous</button>
                            <button type="button" class="btn btn-primary" id="next-step2">Next</button>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="tab-pane fade" id="step3">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Role *</label>
                                <select name="role" id="role" class="form-select" required>
                                    <option value="" disabled selected>Select</option>
                                    <option value="MSWD Representative">MSWD Representative</option>
                                    <option value="Barangay Representative">Barangay Representative</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Username *</label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="showPassword">
                            <label class="form-check-label" for="showPassword">Show Password</label>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" id="prev-step3">Previous</button>
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Messages -->
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalMessage"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/AddMember.js') }}"></script>
@endsection
