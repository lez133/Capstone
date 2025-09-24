@extends('layouts.brgylayout')

@section('title', 'View Profile')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
    .profile-card {
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        background-color: #fff;
    }
    .profile-card .profile-header {
        background: linear-gradient(to right, #28a745, #218838);
        color: white;
        padding: 20px;
        text-align: center;
    }
    .profile-card .profile-header img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        margin-top: -60px;
    }
    .profile-card .profile-body {
        padding: 20px;
    }
    .profile-card .profile-body h5 {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .profile-card .profile-body p {
        font-size: 0.9rem;
        color: #6c757d;
    }
    .profile-card .profile-footer {
        background-color: #f8f9fa;
        padding: 15px;
        text-align: center;
    }
    .profile-card .profile-footer a {
        margin: 0 5px;
    }
</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card profile-card">
                <!-- Profile Header -->
                <div class="profile-header">
                    <img src="{{ $representative->profile_picture ? asset('storage/' . $representative->profile_picture) : asset('images/default-profile.png') }}" alt="Profile Picture">
                    <h4 class="mt-3">{{ $representative->fname }} {{ $representative->lname }}</h4>
                    <p>Barangay Representative</p>
                </div>

                <!-- Profile Body -->
                <div class="profile-body">
                    <h5>Personal Information</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>First Name:</strong> {{ $representative->fname }}</p>
                            <p><strong>Middle Name:</strong> {{ $representative->mname ?? 'N/A' }}</p>
                            <p><strong>Last Name:</strong> {{ $representative->lname }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Gender:</strong> {{ $representative->gender }}</p>
                            <p><strong>Birthday:</strong> {{ $representative->birthday }}</p>
                            <p><strong>Contact:</strong> {{ $representative->contact }}</p>
                        </div>
                    </div>

                    <h5 class="mt-4">Account Information</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Email:</strong> {{ $representative->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Username:</strong> {{ $representative->username }}</p>
                        </div>
                    </div>
                </div>

                <!-- Profile Footer -->
                <div class="profile-footer">
                    <a href="{{ route('representatives.edit', Crypt::encrypt($representative->id)) }}" class="btn btn-primary">
                        <i class="fa fa-edit"></i> Edit Profile
                    </a>
                    <a href="{{ route('representatives.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to Representatives
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
