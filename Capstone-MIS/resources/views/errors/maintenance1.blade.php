@extends('layouts.maintenance')

@section('title', 'Maintenance')

@section('content')
<style>
    .maintenance-container {
        min-height: 70vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }
    .maintenance-icon {
        font-size: 5rem;
        color: #0d6efd;
        margin-bottom: 1rem;
        animation: spin 2s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(-10deg);}
        50% { transform: rotate(10deg);}
        100% { transform: rotate(-10deg);}
    }
    .maintenance-title {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .maintenance-message {
        font-size: 1.15rem;
        color: #6c757d;
        margin-bottom: 2rem;
    }
</style>
<div class="maintenance-container">
    <div>
        <i class="bi bi-tools maintenance-icon"></i>
        <div class="maintenance-title">We'll Be Back Soon!</div>
        <div class="maintenance-message">
            Sorry, this page is currently unavailable.<br>
            It may be under maintenance, incomplete, or an error has occurred.<br>
            Please try again later or contact support if the issue persists.
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-outline-primary rounded-pill px-4">
            <i class="bi bi-arrow-left"></i> Go Back
        </a>
    </div>
</div>
@endsection
