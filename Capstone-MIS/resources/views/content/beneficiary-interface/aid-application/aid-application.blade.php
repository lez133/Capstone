@extends('layouts.beneficiarieslayout')

@section('content')
<!-- ✅ Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/aid-application.css') }}">

<div class="container-fluid px-0 px-md-2">
    <div class="mb-4">
        <h2 class="fw-bold mb-1" style="font-size:2rem;">
            <i class="bi bi-file-earmark-text text-primary me-2"></i>
            <span class="applications-title-underline">Applications</span>
        </h2>
        <p class="text-muted mb-3">Manage and track your aid applications</p>
    </div>

    <!-- 🔍 Search & Filter -->
    <div class="row mb-3 g-2 aid-search-row">
        <div class="col-12 col-md-6 col-lg-4">
            <input type="text" class="form-control" placeholder="Search by application type or ID...">
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <select class="form-select">
                <option>All Status</option>
                <option>Active</option>
                <option>History</option>
            </select>
        </div>
        <div class="col-6 col-md-3 col-lg-2 ms-auto text-end">
            <a href="#" class="btn btn-primary fw-semibold px-4 py-2" style="background:#8b5cf6; border:none;">
                <i class="bi bi-plus-lg me-1"></i> New Application
            </a>
        </div>
    </div>

    <!-- 🧭 Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-row gap-2">
                <button class="btn aid-tab-btn active">Active (3)</button>
                <button class="btn aid-tab-btn">History (2)</button>
            </div>
        </div>
    </div>

    <!-- 📝 Applications -->
    <div class="row g-4">
        @foreach($applications as $app)
        <div class="col-12 col-md-6">
            <div class="card aid-card shadow-sm border-0 h-100
                {{ $app['status_type'] === 'primary' ? 'aid-card-primary' : 'aid-card-danger' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold mb-0">{{ $app['type'] }}</h5>
                        <span class="badge bg-light text-{{ $app['status_type'] }} border border-{{ $app['status_type'] }} fw-semibold px-3 py-2">
                            {{ $app['status_type'] === 'primary' ? '🗓️' : '✔️' }} {{ $app['status'] }}
                        </span>
                    </div>
                    <span class="badge bg-secondary-subtle text-dark mb-2">{{ $app['id'] }}</span>
                    <p class="mb-2 text-muted">{{ $app['description'] }}</p>

                    <div class="mb-3">
                        <div class="fw-semibold text-muted mb-1">Amount</div>
                        <div class="{{ $app['status_type'] === 'primary' ? 'amount-box' : 'amount-box amount-box-success' }}">
                            ₱{{ number_format($app['amount'], 0) }}
                        </div>
                        <div class="fw-semibold text-muted mb-1 mt-2">Progress</div>
                        <div class="progress">
                            <div class="progress-bar bg-{{ $app['status_type'] }}" style="width: {{ $app['progress'] }}%"></div>
                        </div>
                        <small class="text-muted">{{ $app['progress'] }}%</small>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col">
                            <div class="fw-semibold text-muted">Applied</div>
                            <div class="text-dark">{{ $app['applied'] }}</div>
                        </div>
                        <div class="col">
                            <div class="fw-semibold text-muted">Last Update</div>
                            <div class="text-dark">{{ $app['updated'] }}</div>
                        </div>
                    </div>

                    @if($app['distribution_date'])
                    <div class="distribution-date-box">
                        <div class="fw-semibold text-muted mb-1">Distribution Date</div>
                        <div class="fw-bold text-primary">{{ $app['distribution_date'] }}</div>
                    </div>
                    @endif

                    @if($app['can_apply'])
                    <a href="#"
                       class="btn btn-primary mt-2 w-100 show-requirements-btn"
                       data-requirements="{{ implode(',', $app['requirements']) }}"
                       data-app="{{ $app['id'] }}">
                        <i class="bi bi-check-circle me-1"></i> Apply Now
                    </a>
                    @else
                    <div class="alert alert-info mt-2">
                        Application opens on {{ $app['applied'] }}.
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- 💬 Modal -->
<div class="modal fade" id="requirementsModal" tabindex="-1" aria-labelledby="requirementsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="requirementsModalLabel">Requirements</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="requirementsModalBody">
        <!-- Requirements injected here -->
      </div>
    </div>
  </div>
</div>

<!-- ✅ Bootstrap Bundle (JS + Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.show-requirements-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            // Get requirements
            const reqs = btn.getAttribute('data-requirements');
            const reqArr = reqs ? reqs.split(',') : [];
            let html = '<ul>';
            if (reqArr.length && reqArr[0] !== '') {
                reqArr.forEach(function(r) {
                    html += '<li>' + r + '</li>';
                });
            } else {
                html += '<li>No requirements for this aid program.</li>';
            }
            html += '</ul>';
            document.getElementById('requirementsModalBody').innerHTML = html;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('requirementsModal'));
            modal.show();
        });
    });
});
</script>
@endpush
@endsection
