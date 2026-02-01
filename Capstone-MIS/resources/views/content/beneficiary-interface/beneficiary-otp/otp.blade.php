@extends('layouts.beneficiarieslayout')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">OTP Verification</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('beneficiary.otp.verify') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="otp_code" class="form-label">Enter OTP sent to your email or phone</label>
                            <input type="text" name="otp_code" id="otp_code" class="form-control" required maxlength="6">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
                    </form>

                    @if(session('success'))
                        <div class="alert alert-success mt-3">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
                    @endif

                    <hr>

                    <form method="POST" action="{{ route('beneficiary.otp.resend') }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Send OTP via</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="method" id="method_email" value="email" checked>
                                    <label class="form-check-label" for="method_email">Email</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="method" id="method_sms" value="sms">
                                    <label class="form-check-label" for="method_sms">SMS (phone)</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-link w-100 mt-2">Send OTP Again</button>
                        <div class="form-text small text-muted mt-2">
                            Choose SMS to receive OTP on your registered phone number. Ensure your phone number is valid in your account.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
