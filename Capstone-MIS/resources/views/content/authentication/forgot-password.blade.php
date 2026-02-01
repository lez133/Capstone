@extends('layouts.apps')

@section('title', 'Forgot Password')

@section('content')
<div class="container py-4">
    <h4>Forgot password</h4>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    {{-- Debug flash from controller (masked) --}}
    @if(session('fp_debug'))
        <div class="alert alert-warning small">
            <strong>Debug:</strong> {{ session('fp_debug') }}
        </div>
    @endif

    {{-- Show a limited, masked excerpt of recent logs only in debug mode --}}
    @if(config('app.debug') && file_exists(storage_path('logs/laravel.log')))
        @php
            $lines = array_filter(array_map('trim', explode("\n", file_get_contents(storage_path('logs/laravel.log')))));
            $last = array_slice($lines, -8);
            $mask = function($s){
                $s = preg_replace('/\b(\d{6,})\b/', '***masked***', $s);
                $s = preg_replace('/(PHILSMS_TOKEN|PASSWORD|API_KEY|SECRET)=([^\\s]+)/i', '$1=***masked***', $s);
                return $s;
            };
        @endphp
        <div class="alert alert-secondary small">
            <strong>Recent logs (masked):</strong>
            <pre style="white-space:pre-wrap;margin:0;">@foreach($last as $l){{ $mask($l) . "\n" }}@endforeach</pre>
        </div>
    @endif

    <form method="POST" action="{{ route('auth.password.request.post') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Email or Mobile number</label>
            <input
                name="identifier"
                type="text"
                class="form-control"
                placeholder="Enter your email or mobile number"
                value="{{ old('identifier') }}"
                required
                maxlength="255"
                autocomplete="off">
            @if($errors->has('identifier'))
                <div class="text-danger small">{{ $errors->first('identifier') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <button class="btn btn-primary" type="submit">Request password reset</button>
            <a href="{{ route('login') }}" class="btn btn-link">Back to login</a>
        </div>
    </form>

    <small class="text-muted">
        For security, we do not reveal whether the account exists. If a matching account exists, you'll receive instructions.
    </small>
</div>
@endsection
