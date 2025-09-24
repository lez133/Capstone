<section id="features" class="features-section py-5">
  <div class="container text-center">
    <h2 class="fw-bold mb-3">Comprehensive Government Information Management</h2>
    <p class="text-muted mb-5">
      Powerful tools designed to streamline government data management and ensure transparent public service delivery
    </p>

    @php
      $features = [
        ['icon' => 'fas fa-users', 'title' => 'Citizen Registry', 'description' => 'Centralized database for municipal residents.'],
        ['icon' => 'fas fa-file-alt', 'title' => 'Document Management', 'description' => 'Upload, track, and validate government documents.'],
        ['icon' => 'fas fa-calendar-check', 'title' => 'Service Tracking', 'description' => 'Schedule, monitor, and update government service delivery.'],
      ];
    @endphp

    <div class="row g-4">
      <!-- Feature Cards -->
      @foreach ($features as $feature)
        <div class="col-md-4">
          <div class="feature-card p-4 h-100">
            <div class="feature-icon mb-3">
              <i class="{{ $feature['icon'] }} fa-2x text-primary"></i>
            </div>
            <h5 class="fw-bold">{{ $feature['title'] }}</h5>
            <p class="text-muted">{{ $feature['description'] }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
