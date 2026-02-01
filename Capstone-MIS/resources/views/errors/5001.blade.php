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
        @php
            $status = $exception->getStatusCode() ?? 404;
            $messages = [
                404 => [
                    'title' => 'Page Not Found',
                    'icon' => 'bi bi-exclamation-triangle',
                    'message' => "Sorry, the page you are looking for doesn't exist or has been moved."
                ],
                500 => [
                    'title' => 'Server Error',
                    'icon' => 'bi bi-bug-fill',
                    'message' => "Oops! Something went wrong on our end. Please try again later."
                ],
                503 => [
                    'title' => 'Maintenance Mode',
                    'icon' => 'bi bi-tools',
                    'message' => "We're currently performing maintenance. Please check back soon."
                ],
                'default' => [
                    'title' => 'Unavailable',
                    'icon' => 'bi bi-tools',
                    'message' => "Sorry, this page is currently unavailable. Please try again later."
                ]
            ];
            $data = $messages[$status] ?? $messages['default'];
        @endphp
        <i class="bi bi-tools maintenance-icon"></i>
        <div class="maintenance-title">We'll Be Back Soon!</div>
        <div class="maintenance-message">
            {{ $data['message'] }}
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-outline-primary rounded-pill px-4">
            <i class="bi bi-arrow-left"></i> Go Back
        </a>
    </div>
</div>
@endsection
