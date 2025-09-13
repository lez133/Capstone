@extends('layouts.adminlayout')

@section('title', 'Add Member')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add Member</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
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
                    <!-- Step 1: Personal Information -->
                    <div class="tab-pane fade show active" id="step1">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="fname" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="fname" id="fname" class="form-control" placeholder="Enter first name" required>
                            </div>
                            <div class="col-md-4">
                                <label for="mname" class="form-label">Middle Name (Optional)</label>
                                <input type="text" name="mname" id="mname" class="form-control" placeholder="Enter middle name">
                            </div>
                            <div class="col-md-4">
                                <label for="lname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="lname" id="lname" class="form-control" placeholder="Enter last name" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="birth_day" class="form-label">Day <span class="text-danger">*</span></label>
                                <input type="number" name="birth_day" id="birth_day" class="form-control" placeholder="Enter day (1-31)" min="1" max="31" required>
                            </div>
                            <div class="col-md-4">
                                <label for="birth_month" class="form-label">Month <span class="text-danger">*</span></label>
                                <input type="number" name="birth_month" id="birth_month" class="form-control" placeholder="Enter month (1-12)" min="1" max="12" required>
                            </div>
                            <div class="col-md-4">
                                <label for="birth_year" class="form-label">Year <span class="text-danger">*</span></label>
                                <input type="number" name="birth_year" id="birth_year" class="form-control" placeholder="Enter year (e.g., 1990)" min="1900" max="{{ date('Y') }}" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select name="gender" id="gender" class="form-select" required>
                                    <option value="" disabled selected>Select gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="profile_picture" class="form-label">Profile Picture (Optional)</label>
                                <input type="file" name="profile_picture" id="profile_picture" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary" id="next-step1">Next</button>
                        </div>
                    </div>

                    <!-- Step 2: Contact Information -->
                    <div class="tab-pane fade" id="step2">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="contact" class="form-label">Contact Number <span class="text-danger">*</span></label>
                                <input type="number" name="contact" id="contact" class="form-control" placeholder="Enter contact number" required>
                                <small id="contact-error" class="text-danger"></small> <!-- Error message placeholder -->
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Enter email address" required>
                                <small id="email-error" class="text-danger"></small> <!-- Error message placeholder -->
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" id="prev-step2">Previous</button>
                            <button type="button" class="btn btn-primary" id="next-step2">Next</button>
                        </div>
                    </div>

                    <!-- Step 3: Role and Password -->
                    <div class="tab-pane fade" id="step3">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="role" id="role" class="form-select" required>
                                    <option value="" disabled selected>Select role</option>
                                    <option value="MSWD Representative">MSWD Representative</option>
                                    <option value="Barangay Representative">Barangay Representative</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" required>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Retype password" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="showPassword">
                                    <label class="form-check-label" for="showPassword">Show Password</label>
                                </div>
                            </div>
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

<!-- Modal for Message -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalMessage">
                <!-- Message -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/AddMember.js') }}"></script>
@endsection
