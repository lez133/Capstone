{{-- filepath: c:\Lara\Capstone-MIS\resources\views\content\admin-interface\view-profiles\view-profile.blade.php --}}

@extends('layouts.adminlayout')

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
    .profile-note-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.07);
        padding: 24px 22px;
        min-width: 0;
        flex: 0.8;
    }
    .profile-note-title {
        font-size: 1.08rem;
        font-weight: 600;
        color: #222;
        margin-bottom: 18px;
        letter-spacing: 0.5px;
    }
    .profile-note-value {
        background: #e0f7fa;
        color: #218838;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 1rem;
        margin-bottom: 12px;
    }
    .profile-remark-value {
        background: #fff3e0;
        color: #c82333;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 1rem;
    }
    @media (max-width: 900px) {
        .profile-main-container { max-width: 98vw; }
        .profile-header-card { flex-direction: column; align-items: flex-start; }
        .profile-header-info { margin-left: 0; margin-top: 18px; }
        .profile-header-actions { margin-left: 0; margin-top: 18px; }
        .profile-section-row { flex-direction: column; gap: 18px; }
    }
</style>

<div class="profile-main-container">
    <div class="profile-header-card position-relative">
        <img src="{{ asset('img/profile-bg.png') }}" alt="Profile Background" class="profile-header-bg">
        <img
            src="{{ $member->profile_picture
                ? asset('storage/' . $member->profile_picture)
                : asset('img/default-profile.png')
            }}"
            alt="Profile Picture"
            class="profile-picture-lg"
        >
        <div class="profile-header-info">
            <h2>{{ $member->fname }} {{ $member->mname ? $member->mname : '' }} {{ $member->lname }}</h2>
            <div class="profile-id">{{ $member->username }}</div>
            <div>
                <span class="badge bg-primary">{{ $member->role }}</span>
            </div>
        </div>
        <div class="profile-header-actions">
            <a href="#" class="btn btn-primary">
                <i class="fa fa-upload"></i> Upload new photo
            </a>
            <a href="{{ route('members.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to Members
            </a>
        </div>
    </div>

    <div class="profile-section-row">
        <div class="profile-section-card">
            <div class="profile-section-title">About</div>
            <ul class="profile-info-list">
                <li>
                    <span class="profile-info-label"><i class="fa fa-user"></i> Full Name:</span>
                    <span class="profile-info-value">{{ $member->fname }} {{ $member->mname ?? '' }} {{ $member->lname }}</span>
                </li>
                <li>
                    <span class="profile-info-label"><i class="fa fa-mars"></i> Gender:</span>
                    <span class="profile-info-value">{{ $member->gender }}</span>
                </li>
                <li>
                    <span class="profile-info-label"><i class="fa fa-calendar"></i> Birthday:</span>
                    <span class="profile-info-value">{{ \Carbon\Carbon::parse($member->birthday)->format('F d, Y') }}</span>
                </li>
                <li>
                    <span class="profile-info-label"><i class="fa fa-phone"></i> Contact:</span>
                    <span class="profile-info-value">{{ $member->contact }}</span>
                </li>
            </ul>
        </div>
        <div class="profile-section-card">
            <div class="profile-section-title">Account Information</div>
            <ul class="profile-info-list">
                <li>
                    <span class="profile-info-label"><i class="fa fa-envelope"></i> Email:</span>
                    <span class="profile-info-value">{{ $member->email }}</span>
                </li>
                <li>
                    <span class="profile-info-label"><i class="fa fa-user-circle"></i> Username:</span>
                    <span class="profile-info-value">{{ $member->username }}</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
