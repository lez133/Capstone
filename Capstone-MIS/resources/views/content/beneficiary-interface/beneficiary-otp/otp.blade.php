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
                            <label for="otp_code" class="form-label">Enter OTP sent to your email</label>
                            <input type="text" name="otp_code" id="otp_code" class="form-control" required maxlength="6">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
                    </form>
                    @if(session('error'))
                        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('beneficiary.otp.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link w-100 mt-2">Send OTP Again</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
