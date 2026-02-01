<section id="services" class="services-section py-5 bg-light" data-aos="fade-up">
  <div class="container text-center">
    <h2 class="fw-bold mb-3">Our Government Services</h2>
    <p class="text-muted mb-5">
      Comprehensive government services designed to serve and empower our citizens
    </p>

    @php
      $services = [
        [
          'icon' => 'fas fa-file-alt',
          'title' => 'Document Processing',
          'description' => 'Government document processing.',
          'details' => ['Certificates & IDs', 'Verifying submitted documents'],
          'button_text' => 'Apply Now',
        ],
        [
          'icon' => 'fas fa-users',
          'title' => 'Citizen Services',
          'description' => 'Comprehensive citizen support.',
          'details' => ['Resident registration', 'Community programs'],
          'button_text' => 'Access Services',
        ],
        [
          'icon' => 'fas fa-hand-holding-heart',
          'title' => 'Aid Distribution',
          'description' => 'Efficient distribution of government aid.',
          'details' => ['Food packs', 'Financial assistance'],
          'button_text' => 'Learn More',
        ],
        [
          'icon' => 'fas fa-id-card',
          'title' => 'Beneficiary Management',
          'description' => 'Digital registration and tracking of PWDs and senior citizens.',
          'details' => ['Register beneficiaries', 'Track aid distribution', 'Send SMS notifications'],
          'button_text' => 'Manage Beneficiaries',
        ],
      ];
    @endphp

    <div class="row g-4">
      <!-- Service Cards -->
      @foreach ($services as $service)
        <div class="col-md-3" data-aos="zoom-in" data-aos-delay="{{ $loop->index * 100 }}">
          <div class="service-card p-4 h-100">
            <div class="service-icon mb-3">
              <i class="{{ $service['icon'] }} fa-2x text-primary"></i>
            </div>
            <h5 class="fw-bold">{{ $service['title'] }}</h5>
            <p class="text-muted">{{ $service['description'] }}</p>
            <ul class="text-start text-muted small">
              @foreach ($service['details'] as $detail)
                <li>{{ $detail }}</li>
              @endforeach
            </ul>
            <a href="#" class="btn btn-primary w-100">{{ $service['button_text'] }}</a>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
