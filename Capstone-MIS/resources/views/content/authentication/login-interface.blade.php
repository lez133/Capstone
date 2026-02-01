@extends('layouts.apps')

@section('content')
@include('partials.Landing-page.navbar')
@include('partials.Landing-page.hero')
@include('partials.Landing-page.features')
@include('partials.Landing-page.services')
@include('partials.Landing-page.footer')
@include('partials.Landing-page.privacy-policy-modal')
@include('partials.Landing-page.terms-of-service-modal')
@include('partials.Landing-page.contact-us-modal')

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">

            <div class="row g-0">

                <!-- LEFT SIDE IMAGE -->
                <div class="col-md-5 d-none d-md-block">
                    <img src="{{ asset('img/Login-Interface.png') }}"
                         alt="Login Illustration"
                         class="w-100 h-100 object-fit-cover">
                </div>

                <!-- RIGHT SIDE CONTENT -->
                <div class="col-md-7 p-4 p-md-5">

                    <div class="modal-header border-0 px-0 mb-4">
                        <h4 class="modal-title fw-bold text-dark">
                            <i class="fas fa-user-circle me-2 text-primary"></i>Welcome Back
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body px-0">

                        @if ($errors->has('username'))
                            <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-3" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Login Failed!</strong>
                                {{ $errors->first('username') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->has('password'))
                            <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-3" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Login Failed!</strong>
                                {{ $errors->first('password') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('login') }}" method="POST" id="loginForm">
                            @csrf

                            <!-- Username -->
                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold text-dark">
                                    <i class="fas fa-user me-2 text-primary"></i>Username
                                </label>
                                <input
                                    type="text"
                                    name="username"
                                    id="username"
                                    class="form-control form-control-lg rounded-3 border-2"
                                    value="{{ old('username') }}"
                                    placeholder="Enter your username"
                                    required>
                            </div>

                            <!-- Password -->
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold text-dark">
                                    <i class="fas fa-lock me-2 text-primary"></i>Password
                                </label>

                                <div class="input-group input-group-lg">
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="form-control rounded-start-3 border-2"
                                        placeholder="Enter your password"
                                        required>
                                    <button class="btn btn-outline-secondary rounded-end-3 border-2" type="button" id="togglePassword" aria-label="Toggle password visibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>

                                {{-- <!-- Direct link to dedicated forgot-password page -->
                                <div class="mt-2">
                                    <a href="{{ route('auth.password.request') }}" class="small">Forgot password? (email or mobile)</a>
                                </div> --}}
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mt-2 shadow-sm rounded-3 fw-semibold" id="loginBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<!-- removed modal auto-open script since forgot-password is now a separate page -->
@endpush

@endsection
