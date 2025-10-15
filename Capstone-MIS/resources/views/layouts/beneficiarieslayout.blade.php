<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficiary Portal</title>
    <link rel="icon" href="{{ asset('img/mswd-logo.jpg') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/BeneficiaryUI.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

@php
    $user = Auth::guard('beneficiary')->user();
    $currentRoute = Route::currentRouteName();
@endphp

@if(!$user || !$user->verified)
    <div class="d-flex flex-column justify-content-center align-items-center vh-100">
        <div class="alert alert-warning text-center shadow">
            <h4 class="fw-bold mb-2">Account Not Verified</h4>
            <p>Your account is not yet verified by the admin.<br>
            Please wait for verification before accessing the portal.</p>
            <form action="{{ route('logout') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">Sign Out</button>
            </form>
        </div>
    </div>
@elseif(!$user->otp_confirmed && $currentRoute !== 'beneficiary.otp')
    <script>window.location = "{{ route('beneficiary.otp') }}";</script>
@else
    <div class="d-flex">
        {{-- Sidebar --}}
        @include('partials.Beneficiarypartials.beneficiary-sidebar')

        {{-- Main Content --}}
        <div id="mainContent" class="flex-grow-1" style="margin-left:260px;">
            {{-- Header --}}
            @include('partials.Beneficiarypartials.beneficiary-header')

            <main class="p-4">
                @yield('content')
            </main>
        </div>
    </div>
@endif
@stack('scripts')
<script src="{{ asset('js/BeneficiaryUi.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
