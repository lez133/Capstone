@extends('layouts.brgylayout')

@section('title', 'Change Password')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
    .settings-container {
        max-width: 430px;
        margin: 40px auto;
    }
    .settings-card {
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.09);
        background: #fff;
        overflow: hidden;
        padding: 0;
    }
    .settings-card-header {
        background: linear-gradient(120deg, #28a745 60%, #218838 100%);
        color: #fff;
        padding: 32px 24px 24px 24px;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
        text-align: center;
    }
    .settings-card-header h5 {
        font-size: 1.35rem;
        font-weight: 600;
        margin-bottom: 0;
        letter-spacing: 1px;
    }
    .settings-card-body {
        padding: 32px 24px 24px 24px;
    }
    .settings-card .form-label {
        font-weight: 500;
        color: #28a745;
    }
    .settings-card .form-control {
        border-radius: 8px;
        font-size: 1rem;
        margin-bottom: 18px;
    }
    .settings-card .btn-primary {
        border-radius: 8px;
        font-weight: 600;
        letter-spacing: 0.5px;
        padding: 10px 0;
        font-size: 1.08rem;
        box-shadow: 0 2px 8px rgba(40,167,69,0.08);
    }
    .alert {
        border-radius: 8px;
        font-size: 0.98rem;
        margin-bottom: 18px;
    }
</style>

<div class="settings-container">
    <div class="settings-card shadow-sm">
        <div class="settings-card-header">
            <h5><i class="fa fa-user-cog me-2"></i> Change Password</h5>
        </div>
        <div class="settings-card-body">
            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2 auto-hide-alert" style="background:linear-gradient(90deg,#e0ffe0,#c6f7d4);color:#218838;border:none;">
                    <i class="fa fa-check-circle fa-lg"></i>
                    <span class="fw-semibold">{{ session('success') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger d-flex align-items-center gap-2 auto-hide-alert" style="background:linear-gradient(90deg,#ffe0e0,#f7c6c6);color:#c82333;border:none;">
                    <i class="fa fa-exclamation-triangle fa-lg"></i>
                    <ul class="mb-0" style="list-style:none;padding-left:0;">
                        @foreach($errors->all() as $err)
                            <li class="fw-semibold">{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="alert alert-info d-flex align-items-center gap-2" style="background:linear-gradient(90deg,#e0f7ff,#c6e7f7);color:#0c5460;border:none;">
                <i class="fa fa-info-circle fa-lg"></i>
                <span>
                    Password must be at least <strong>8 characters</strong> and contain at least
                    <strong>one uppercase letter</strong>, <strong>one lowercase letter</strong>, and <strong>one number</strong>.
                </span>
            </div>

            <form method="POST" action="{{ route('brgyrep.password.update') }}">
                @csrf

                <label for="current_password" class="form-label">Current Password</label>
                <input
                    type="password"
                    name="current_password"
                    id="current_password"
                    class="form-control"
                    required autocomplete="current-password">

                <label for="new_password" class="form-label">New Password</label>
                <input
                    type="password"
                    name="new_password"
                    id="new_password"
                    class="form-control"
                    required autocomplete="new-password">

                <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                <input
                    type="password"
                    name="new_password_confirmation"
                    id="new_password_confirmation"
                    class="form-control"
                    required autocomplete="new-password">

                {{-- <div class="form-check mt-2 mb-3">
                    <input class="form-check-input" type="checkbox" id="showBothPasswords">
                    <label class="form-check-label" for="showBothPasswords">
                        Show Passwords
                    </label>
                </div> --}}

                <button type="submit" class="btn btn-primary w-100 mt-2">
                    <i class="fa fa-key me-1"></i> Update Password
                </button>
            </form>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
window.addEventListener('DOMContentLoaded', function() {

    var showBothCheckbox = document.getElementById('showBothPasswords');

    var currentInput = document.getElementById('current_password');
    var newInput = document.getElementById('new_password');
    var confirmInput = document.getElementById('new_password_confirmation');

    if (showBothCheckbox) {
        showBothCheckbox.addEventListener('change', function() {
            var type = this.checked ? 'text' : 'password';
            currentInput.type = type;
            newInput.type = type;
            confirmInput.type = type;
        });
    }

    // Auto-hide alerts
    var alerts = document.querySelectorAll('.auto-hide-alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 3000);
    });

});
</script>
@endsection
