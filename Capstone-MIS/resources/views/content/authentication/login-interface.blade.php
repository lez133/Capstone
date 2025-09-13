@extends('layouts.apps')

@section('content')

<!-- Accessibility Button & Panel -->
<div class="accessibility-container">
    <button class="accessibility-toggle" aria-label="Open Accessibility Menu">
        <i class="fas fa-universal-access me-2"></i><span>Accessibility</span>
    </button>

    <div class="accessibility-panel">
        <!-- Mobile Close Button -->
        <button class="accessibility-close d-md-none" aria-label="Close Accessibility Menu" style="float:right; margin-bottom:10px;">&times;</button>
        <h2><i class="fas fa-cogs me-2"></i>Accessibility Settings</h2>

        <!-- Category: Text & Readability -->
        <div class="category">
            <h3><i class="fas fa-font me-2"></i>Text & Readability</h3>
            <div class="category-options">
                <button data-action="font-size" data-size="small"><i class="fas fa-text-height me-2"></i>Small Text</button>
                <button data-action="font-size" data-size="normal"><i class="fas fa-text-height me-2"></i>Normal Text</button>
                <button data-action="font-size" data-size="large"><i class="fas fa-text-height me-2"></i>Large Text</button>
            </div>
        </div>

        <!-- Category: Color & Contrast -->
        <div class="category">
            <h3><i class="fas fa-adjust me-2"></i>Color & Contrast</h3>
            <div class="category-options">
                <button data-action="contrast" data-mode="normal"><i class="fas fa-eye me-2"></i>Normal</button>
                <button data-action="contrast" data-mode="high"><i class="fas fa-low-vision me-2"></i>High Contrast</button>
                <button data-action="contrast" data-mode="grayscale"><i class="fas fa-palette me-2"></i>Grayscale</button>
                <button data-action="contrast" data-mode="negative"><i class="fas fa-adjust me-2"></i>Negative</button>
            </div>
        </div>

        <!-- Category: Links & Navigation -->
        <div class="category">
            <h3><i class="fas fa-link me-2"></i>Links & Navigation</h3>
            <div class="category-options">
                <button data-action="links" data-mode="underline"><i class="fas fa-underline me-2"></i>Underline Links</button>
                <button data-action="links" data-mode="highlight"><i class="fas fa-highlighter me-2"></i>Highlight Links</button>
                <button data-action="links" data-mode="normal"><i class="fas fa-link me-2"></i>Normal Links</button>
            </div>
        </div>

        <button class="reset-btn" data-action="reset"><i class="fas fa-redo me-2"></i>Reset All</button>
    </div>
</div>


<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-white sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="{{ asset('img/mswd-logo.png') }}" alt="MSWD Logo" height="40" class="me-2">
            <span class="fw-bold text-primary">MSWD Anhawan branch</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-3">
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-home me-2"></i>Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-info-circle me-2"></i>About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-newspaper me-2"></i>News & Events</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-phone-alt me-2"></i>Contact Us</a></li>
            </ul>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="fas fa-sign-in-alt me-2"></i>Login</button>
        </div>
    </div>
</nav>

<!-- Banner Carousel -->
<div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="{{ asset('img/helping_bg.jpg') }}" class="d-block w-100 banner-img" alt="Banner">
            <div class="carousel-caption">
                <h1 class="display-4"><i class="fas fa-hands-helping me-2"></i>Empowering Communities</h1>
                <p class="lead">Together, we can make a difference.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="fas fa-arrow-right me-2"></i>Get Started</button>
            </div>
        </div>
    </div>
</div>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title"><i class="fas fa-user-circle me-2"></i>Welcome Back</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Google sign-in -->
        <a href="{{ route('login.google') }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center mb-3" aria-label="Sign in with Google">
            <svg width="18" height="18" viewBox="0 0 533.5 544.3" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
              <path fill="#4285f4" d="M533.5 278.4c0-18.6-1.5-36.5-4.3-53.9H272v102h147.3c-6.4 34.9-25.5 64.4-54.4 84.3v69h87.8c51.4-47.4 80.8-117.4 80.8-201.4z"/>
              <path fill="#34a853" d="M272 544.3c73.6 0 135.4-24.4 180.5-66.4l-87.8-69c-24.4 16.4-55.7 26.1-92.7 26.1-71 0-131.2-47.9-152.7-112.4H29.6v70.9C74.6 489.1 166.3 544.3 272 544.3z"/>
              <path fill="#fbbc04" d="M119.3 323.6c-10.9-32.6-10.9-67.9 0-100.5V152.2H29.6c-40.3 78.6-40.3 171.3 0 249.9l89.7-78.5z"/>
              <path fill="#ea4335" d="M272 107.6c38.6 0 73.4 13.3 100.8 39.3l75.6-75.6C407.2 24.3 345.4 0 272 0 166.3 0 74.6 55.2 29.6 136.6l89.7 70.9C140.8 155.5 201 107.6 272 107.6z"/>
            </svg>
            <span class="ms-2">Sign in with Google</span>
        </a>

        <div class="text-center my-2"><small class="text-muted">or continue with your ID</small></div>

        <!-- Classic login form -->
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sign-in-alt me-2"></i>Login</button>
        </form>

        <script>
            document.getElementById('togglePassword').addEventListener('click', function () {
                const passwordField = document.getElementById('password');
                const icon = this.querySelector('i');

                // Toggle password visibility
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordField.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        </script>
      </div>
    </div>
  </div>
</div>
@endsection
