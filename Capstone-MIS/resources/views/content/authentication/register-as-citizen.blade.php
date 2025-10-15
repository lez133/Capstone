@extends('layouts.apps')

@section('content')

<style>
    /* small styles for stepper spacing */
    .step { transition: opacity 0.2s ease; }
    .d-none { display: none !important; }
    .bg-light { background-color: #f8f9fa !important; }
</style>

<div class="container py-5">
    <div class="card shadow rounded-4">
        <div class="card-body p-5">
            <h2 class="text-center mb-5 fw-bold">Beneficiary Registration</h2>

            <!-- Stepper -->
            <div class="d-flex justify-content-between mb-5 position-relative">
                <div class="position-absolute start-0 w-100 bg-light" style="height:4px; top:60%;"></div>
                <div id="progressLine" class="position-absolute start-0 bg-success" style="height:4px; width:0%; top:60%; transition: width 0.4s;"></div>

                <div class="text-center flex-fill">
                    <div id="stepIndicator1" class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto"
                         style="width:40px; height:40px;">1</div>
                    <small class="d-block mt-2">Basic Info</small>
                </div>
                <div class="text-center flex-fill">
                    <div id="stepIndicator2" class="rounded-circle bg-light text-dark border d-flex align-items-center justify-content-center mx-auto"
                         style="width:40px; height:40px;">2</div>
                    <small class="d-block mt-2">Personal</small>
                </div>
                <div class="text-center flex-fill">
                    <div id="stepIndicator3" class="rounded-circle bg-light text-dark border d-flex align-items-center justify-content-center mx-auto"
                         style="width:40px; height:40px;">3</div>
                    <small class="d-block mt-2">ID Info</small>
                </div>
                <div class="text-center flex-fill">
                    <div id="stepIndicator4" class="rounded-circle bg-light text-dark border d-flex align-items-center justify-content-center mx-auto"
                         style="width:40px; height:40px;">4</div>
                    <small class="d-block mt-2">Account</small>
                </div>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('register-as-citizen.store') }}" id="registrationForm">
                @csrf

                <!-- Step 1 -->
                <div id="step1" class="step">
                    <h4 class="mb-4">Step 1 of 4: Basic Information</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" name="last_name" id="last_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" name="first_name" id="first_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" name="email" id="email" class="form-control">
                            <small id="email-error" class="text-danger"></small>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="text" name="phone" id="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="middle_name" class="form-label">Middle Name (Optional)</label>
                            <input type="text" name="middle_name" id="middle_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="suffix" class="form-label">Suffix (Optional)</label>
                            <input type="text" name="suffix" id="suffix" class="form-control">
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('login') }}" class="btn btn-secondary">Back to Login</a>
                        <button type="button" onclick="validateStep1()" class="btn btn-primary">Next →</button>
                    </div>
                </div>

                <!-- Step 2 -->
                <div id="step2" class="step d-none">
                    <h4 class="mb-4">Step 2 of 4: Personal Details</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="beneficiary_type" class="form-label">Beneficiary Type *</label>
                            <select name="beneficiary_type" id="beneficiary_type" class="form-select">
                                <option value="">Select Beneficiary Type</option>
                                <option value="Senior Citizen">Senior Citizen</option>
                                <option value="PWD">PWD</option>
                                <option value="Both">Both</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="birthday" class="form-label">Birthday *</label>
                            <input type="date" name="birthday" id="birthday" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="age" class="form-label">Age</label>
                            <input type="text" name="age" id="age" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Gender *</label>
                            <select name="gender" id="gender" class="form-select">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="civil_status" class="form-label">Civil Status *</label>
                            <select name="civil_status" id="civil_status" class="form-select">
                                <option value="">Select Civil Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="barangay_id" class="form-label">Select Barangay *</label>
                            <select name="barangay_id" id="barangay_id" class="form-select" required>
                                <option value="" disabled selected>Select your barangay</option>
                                @foreach ($barangays as $barangay)
                                    <option value="{{ $barangay->id }}">{{ $barangay->barangay_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" onclick="prevStep(1)" class="btn btn-secondary">← Previous</button>
                        <button type="button" onclick="validateStep2()" class="btn btn-primary">Next →</button>
                    </div>
                </div>

                <!-- Step 3 -->
                <div id="step3" class="step d-none">
                    <h4 class="mb-4">Step 3 of 4: ID Information</h4>
                    <div class="row g-3">
                        <div class="col-md-6 senior-only d-none">
                            <label for="osca_number" class="form-label">OSCA Number *</label>
                            <input type="text" name="osca_number" id="osca_number" class="form-control">
                        </div>
                        <div class="col-md-6 pwd-only d-none">
                            <label for="pwd_id" class="form-label">PWD ID *</label>
                            <input type="text" name="pwd_id" id="pwd_id" class="form-control">
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" onclick="prevStep(2)" class="btn btn-secondary">← Previous</button>
                        <button type="button" onclick="validateStep3()" class="btn btn-primary">Next →</button>
                    </div>
                </div>

                <!-- Step 4 -->
                <div id="step4" class="step d-none">
                    <h4 class="mb-4">Step 4 of 4: Account Setup</h4>

                    <div class="mb-4">
                        <h5 class="fw-bold">Account Summary</h5>
                        <div class="border rounded-3 p-3 bg-light">
                            <p><strong>Name:</strong> <span id="summary_name"></span></p>
                            <p><strong>Email:</strong> <span id="summary_email"></span></p>
                            <p><strong>Phone:</strong> <span id="summary_phone"></span></p>
                            <p><strong>Beneficiary Type:</strong> <span id="summary_type"></span></p>
                            <p><strong>Birthday:</strong> <span id="summary_birthday"></span></p>
                            <p><strong>Age:</strong> <span id="summary_age"></span></p>
                            <p><strong>Gender:</strong> <span id="summary_gender"></span></p>
                            <p><strong>Civil Status:</strong> <span id="summary_civil_status"></span></p>
                            <p class="senior-only d-none"><strong>OSCA Number:</strong> <span id="summary_osca"></span></p>
                            <p class="pwd-only d-none"><strong>PWD ID:</strong> <span id="summary_pwd"></span></p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username">
                            <small id="username-error" class="text-danger"></small>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password *</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm Password *</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm your password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password_confirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        {!! NoCaptcha::renderJs() !!}
                        {!! NoCaptcha::display() !!}
                        @if ($errors->has('g-recaptcha-response'))
                            <span class="text-danger">{{ $errors->first('g-recaptcha-response') }}</span>
                        @endif
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" onclick="prevStep(3)" class="btn btn-secondary">← Previous</button>
                        <button type="button" onclick="validateStep4()" class="btn btn-success">✔ Create Account</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Warning Modal -->
<div class="modal fade" id="warningModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">⚠ Warning</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="warningMessage">
        Please fill out all required fields.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('js/citizen-registration.js') }}"></script>
@endsection
