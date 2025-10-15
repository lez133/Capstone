<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('img/mswd-logo.jpg') }}" type="image/x-icon">
    <title>@yield('title', 'Barangay Representative Dashboard')</title>

    <!-- Fonts, Bootstrap & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">


    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/BrgyUI.css') }}">
</head>
<body>
    <div class="d-flex" style="min-height:100vh;">
        @include('partials.Brgypartials.brgy-sidebar')
        <main class="flex-grow-1">
            @include('partials.Brgypartials.brgy-topbar')
            <div class="p-4">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/BrgyUI.js') }}"></script>
</body>
</html>
