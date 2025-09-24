@extends('layouts.apps')

@section('title', 'Too Many Requests')

@section('content')
<div class="container text-center py-5">
    <h1 class="display-4 text-danger">429 - Too Many Requests</h1>
    <p class="lead">{{ $message }}</p>
    <p>Please wait a moment and try again.</p>
    <a href="{{ route('login') }}" class="btn btn-primary mt-3">Back to Login</a>
</div>
@endsection
