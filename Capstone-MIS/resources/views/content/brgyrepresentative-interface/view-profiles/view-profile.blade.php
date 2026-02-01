@extends('layouts.brgylayout')

@section('title', 'View Profile')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
    body { background: #f4f6fb; }
    .profile-main-container {
        max-width: 1100px;
        margin: 40px auto;
    }
    .profile-header-card {
        display: flex;
        align-items: center;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.09);
        padding: 32px 24px;
        margin-bottom: 32px;
        position: relative;
    }
    .profile-header-bg {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        object-fit: cover;
        opacity: 0.18;
        z-index: 1;
        border-radius: 18px;
    }
    .profile-picture-lg {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #e3e3e3;
        background: #fff;
        z-index: 2;
    }
    .profile-header-info {
        margin-left: 32px;
        z-index: 2;
    }
    .profile-header-info h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 6px;
        color: #222;
    }
    .profile-header-info .profile-id {
        font-size: 1.1rem;
        color: #666;
        margin-bottom: 12px;
    }
    .profile-header-actions {
        margin-left: auto;
        z-index: 2;
    }
    .profile-header-actions .btn {
        min-width: 170px;
        margin-bottom: 8px;
        border-radius: 8px;
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    .profile-section-row {
        display: flex;
        gap: 24px;
        margin-bottom: 24px;
    }
    .profile-section-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.07);
        flex: 1;
        padding: 24px 22px;
        min-width: 0;
    }
    .profile-section-title {
        font-size: 1.08rem;
        font-weight: 600;
        color: #222;
        margin-bottom: 18px;
        letter-spacing: 0.5px;
    }
    .profile-info-list {
        padding: 0;
        margin: 0;
        list-style: none;
    }
    .profile-info-list li {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
        font-size: 1rem;
    }
    .profile-info-label {
        font-weight: 500;
        color: #555;
        min-width: 140px;
        display: inline-block;
    }
    .profile-info-value {
        color: #333;
        margin-left: 8px;
        font-weight: 400;
    }
    .form-control, select {
        border-radius: 8px;
        font-size: 1rem;
        margin-bottom: 12px;
        padding: 8px 12px;
        width: 100%;
        border: 1px solid #e3e3e3;
    }
    .btn-primary {
        border-radius: 8px;
        font-weight: 600;
        letter-spacing: 0.5px;
        padding: 10px 0;
        font-size: 1.08rem;
        box-shadow: 0 2px 8px rgba(40,167,69,0.08);
    }
    .btn-secondary {
        border-radius: 8px;
        font-weight: 500;
        letter-spacing: 0.5px;
        padding: 10px 0;
        font-size: 1.08rem;
    }
    .alert {
        border-radius: 8px;
        font-size: 0.98rem;
        margin-bottom: 18px;
    }
    @media (max-width: 900px) {
        .profile-main-container { max-width: 98vw; }
        .profile-header-card { flex-direction: column; align-items: flex-start; }
        .profile-header-info { margin-left: 0; margin-top: 18px; }
        .profile-header-actions { margin-left: 0; margin-top: 18px; }
        .profile-section-row { flex-direction: column; gap: 18px; }
    }
</style>

@php
    $isEditing = request()->query('edit') === '1';
@endphp

<div class="profile-main-container">
    <div class="profile-header-card position-relative">
        <img src="{{ asset('img/profile-bg.png') }}" alt="Profile Background" class="profile-header-bg">
        <img
            src="{{ $representative->profile_picture
                ? asset('storage/' . $representative->profile_picture)
                : asset('img/default-profile.png')
            }}"
            alt="Profile Picture"
            class="profile-picture-lg"
        >
        <div class="profile-header-info">
            <h2>
                @if($isEditing)
                    <input type="text" name="fname" class="form-control" value="{{ old('fname', $representative->fname) }}" style="width:32%;display:inline-block;" form="editProfileForm" required>
                    <input type="text" name="mname" class="form-control" value="{{ old('mname', $representative->mname) }}" style="width:20%;display:inline-block;" form="editProfileForm">
                    <input type="text" name="lname" class="form-control" value="{{ old('lname', $representative->lname) }}" style="width:32%;display:inline-block;" form="editProfileForm" required>
                @else
                    {{ $representative->fname }} {{ $representative->mname ? $representative->mname : '' }} {{ $representative->lname }}
                @endif
            </h2>
            <div class="profile-id">
                @if($isEditing)
                    <input type="text" name="username" class="form-control" value="{{ old('username', $representative->username) }}" style="width:50%;" form="editProfileForm" required>
                @else
                    {{ $representative->username }}
                @endif
            </div>
            <div>
                <span class="badge bg-success">Barangay Representative</span>
            </div>
        </div>
        <div class="profile-header-actions">
            @if($isEditing)
                <form id="editProfileForm" method="POST" action="{{ route('representatives.update', ['encryptedId' => Crypt::encrypt($representative->id)]) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="file" name="profile_picture" class="form-control mb-2" style="width:180px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('brgyrep.profile.view', ['encryptedId' => Crypt::encrypt($representative->id)]) }}" class="btn btn-secondary ms-2">
                        Cancel
                    </a>
                </form>
            @else
                <a href="{{ route('brgyrep.profile.view', ['encryptedId' => Crypt::encrypt($representative->id)]) }}?edit=1" class="btn btn-primary">
                    <i class="fa fa-edit"></i> Edit Profile
                </a>
                <a href="{{ route('representatives.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Representatives
                </a>
            @endif
        </div>
    </div>

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

    <div class="profile-section-row">
        <div class="profile-section-card">
            <div class="profile-section-title">About</div>
            <ul class="profile-info-list">
                <li>
                    <span class="profile-info-label"><i class="fa fa-mars"></i> Gender:</span>
                    <span class="profile-info-value">
                        @if($isEditing)
                            <select name="gender" class="form-control" form="editProfileForm" required>
                                <option value="Male" {{ old('gender', $representative->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $representative->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        @else
                            {{ $representative->gender }}
                        @endif
                    </span>
                </li>
                <li>
                    <span class="profile-info-label"><i class="fa fa-calendar"></i> Birthday:</span>
                    <span class="profile-info-value">
                        @if($isEditing)
                            <input type="date" name="birthday" class="form-control" value="{{ old('birthday', $representative->birthday) }}" form="editProfileForm" required>
                        @else
                            {{ \Carbon\Carbon::parse($representative->birthday)->format('F d, Y') }}
                        @endif
                    </span>
                </li>
                <li>
                    <span class="profile-info-label"><i class="fa fa-phone"></i> Contact:</span>
                    <span class="profile-info-value">
                        @if($isEditing)
                            <input type="text" name="contact" class="form-control" value="{{ old('contact', $representative->contact) }}" form="editProfileForm" required>
                        @else
                            {{ $representative->contact }}
                        @endif
                    </span>
                </li>
            </ul>
        </div>
        <div class="profile-section-card">
            <div class="profile-section-title">Account Information</div>
            <ul class="profile-info-list">
                <li>
                    <span class="profile-info-label"><i class="fa fa-envelope"></i> Email:</span>
                    <span class="profile-info-value">
                        @if($isEditing)
                            <input type="email" name="email" class="form-control" value="{{ old('email', $representative->email) }}" form="editProfileForm" required>
                        @else
                            {{ $representative->email }}
                        @endif
                    </span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
window.addEventListener('DOMContentLoaded', function() {
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
