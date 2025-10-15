@extends('layouts.adminlayout')

@section('title', 'Select Barangay')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Select a Barangay</h1>

    <div class="mt-4">
        <a href="{{ route('beneficiaries.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to beneficiary interface
        </a>
    </div>

    <div class="row">
        @foreach ($barangays as $barangay)
            @php
                $verifiedCount = $barangay->beneficiaries()->where('verified', true)->count();
                $notVerifiedCount = $barangay->beneficiaries()->where('verified', false)->count();
            @endphp
            <div class="col-md-4 mb-4">
                <a href="{{ route('senior-citizens.manage', ['encryptedBarangayId' => Crypt::encrypt($barangay->id)]) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">{{ $barangay->barangay_name }}</h5>
                            <p class="card-text text-muted">
                                Verified: <strong>{{ $verifiedCount }}</strong><br>
                                Not Verified: <strong>{{ $notVerifiedCount }}</strong>
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
