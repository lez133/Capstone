{{-- filepath: c:\Lara\Capstone-MIS\resources\views\partials\Landing-page\hero.blade.php --}}

<section class="hero-section d-flex align-items-center text-center position-relative" data-aos="fade-down">
  <!-- Carousel Background -->
  <div id="heroCarousel" class="carousel slide carousel-fade position-absolute top-0 start-0 w-100 h-100" data-bs-ride="carousel" data-bs-interval="5000" style="z-index: 0;">
    <div class="carousel-inner h-100">
      <div class="carousel-item active h-100">
        <img src="{{ asset('img/mswdbg.jpg') }}" alt="Hero Slide 1">
      </div>
      <div class="carousel-item h-100">
        <img src="{{ asset('img/mswdbg1.jpg') }}" alt="Hero Slide 2">
      </div>
      <div class="carousel-item h-100">
        <img src="{{ asset('img/mswdbg2.jpg') }}" alt="Hero Slide 3">
      </div>
      <div class="carousel-item h-100">
        <img src="{{ asset('img/mswdbg3.jpg') }}" alt="Hero Slide 4">
      </div>
      <div class="carousel-item h-100">
        <img src="{{ asset('img/mswdbg4.jpg') }}" alt="Hero Slide 5">
      </div>
    </div>
  </div>
  <!-- Hero Content -->
  <div class="container position-relative">
    <h1 class="fw-bold display-5 text-white text-shadow" style="text-shadow: 2px 2px 6px #000, 0 0 2px #000, 0 0 1px #000;">
      <span class="text-danger" style="text-shadow: 2px 2px 6px #000, 0 0 2px #000, 0 0 1px #000;">Municipal Information</span> <br>
      <span class="text-primary" style="text-shadow: 2px 2px 6px #000, 0 0 2px #000, 0 0 1px #000;">Management System</span>
    </h1>
    <p class="lead mt-3 text-light text-shadow" style="text-shadow: 2px 2px 6px #000, 0 0 2px #000, 0 0 1px #000;">
      Streamlining government data management and public service delivery for the Municipality of Anahawan.
      Ensuring transparency, efficiency, and accessibility in local government operations.
    </p>
    <div class="mt-4">
      <a href="{{ route('register-as-citizen') }}" class="btn btn-primary me-2">Register Now</a>
      <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
    </div>
  </div>
  <!-- Overlay for better text contrast -->
  <div class="overlay"></div>
</section>
