<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="{{ asset('img/mswd-logo.jpg') }}" alt="MSWD Logo" height="40" class="me-2">
      <span class="fw-bold">MSWD Anahawan</span>
    </a>

    <!-- Toggler for mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Center Navigation -->
    <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item">
          <a class="nav-link fw-medium" href="#features">Features</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-medium" href="#services">Services</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-medium" href="#events">Events</a>
        </li>
      </ul>
    </div>

    <!-- Right side buttons -->
    <div class="d-flex">
      <a href="{{ route('register-as-citizen') }}" class="btn btn-primary me-2">Register as Citizen</a>
      <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
    </div>
  </div>
</nav>
