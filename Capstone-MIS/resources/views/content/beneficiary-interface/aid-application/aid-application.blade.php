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
            <input type="text" class="form-control" placeholder="Search by application type or ID..." id="searchInput">
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <select class="form-select" id="statusFilter">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="history">History</option>
            </select>
        </div>
    </div>

    <!-- 🧭 Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-row gap-2">
                <button class="btn aid-tab-btn active" id="activeTabBtn" data-tab="active">
                    Active ({{ count($activeApplications) }})
                </button>
                <button class="btn aid-tab-btn" id="historyTabBtn" data-tab="history">
                    History ({{ count($historyApplications) }})
                </button>
            </div>
        </div>
    </div>

    <!-- 📝 Applications -->
    <div class="row g-4" id="activeTab" data-tab-content="active">
        @forelse($activeApplications as $app)
        <div class="col-12 col-md-6">
            @include('partials.Beneficiarypartials.aid-application.application-card', ['app' => $app])
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">No active applications found.</div>
        </div>
        @endforelse
    </div>
    <div class="row g-4 d-none" id="historyTab" data-tab-content="history">
        @forelse($historyApplications as $app)
        <div class="col-12 col-md-6">
            @include('partials.Beneficiarypartials.aid-application.aid-application-history', ['app' => $app])
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">No history records found.</div>
        </div>
        @endforelse
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
    // FIXED: Secure tab switching with data attributes
    const tabButtons = document.querySelectorAll('[data-tab]');
    const tabContents = document.querySelectorAll('[data-tab-content]');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const tabName = this.getAttribute('data-tab');

            // Update button states
            tabButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Update tab visibility
            tabContents.forEach(content => {
                if (content.getAttribute('data-tab-content') === tabName) {
                    content.classList.remove('d-none');
                } else {
                    content.classList.add('d-none');
                }
            });
        });
    });

    // FIXED: Secure requirements modal with proper XSS prevention
    document.querySelectorAll('.show-requirements-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            // FIXED: Parse JSON instead of splitting by comma
            const reqsJson = this.getAttribute('data-requirements-json');
            let requirements = [];

            try {
                requirements = reqsJson ? JSON.parse(reqsJson) : [];
            } catch (err) {
                console.error('Invalid requirements format:', err);
                requirements = [];
            }

            // FIXED: Use textContent instead of innerHTML for XSS prevention
            const ul = document.createElement('ul');
            if (requirements.length > 0) {
                requirements.forEach(function(req) {
                    const li = document.createElement('li');
                    li.textContent = req; // FIXED: textContent is XSS-safe
                    ul.appendChild(li);
                });
            } else {
                const li = document.createElement('li');
                li.textContent = 'No requirements for this aid program.';
                ul.appendChild(li);
            }

            // Clear and update modal
            const modalBody = document.getElementById('requirementsModalBody');
            modalBody.innerHTML = ''; // Safe to clear
            modalBody.appendChild(ul);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('requirementsModal'));
            modal.show();
        });
    });

    // FIXED: Search functionality with XSS prevention
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');

    function filterApplications() {
        const searchTerm = String(searchInput.value).toLowerCase().trim();
        const statusFilter = String(document.getElementById('statusFilter').value).toLowerCase().trim();

        document.querySelectorAll('[data-tab-content] .col-12').forEach(col => {
            const card = col.querySelector('.aid-card');
            if (!card) return;

            const appId = card.getAttribute('data-app-id') || '';
            const appType = card.getAttribute('data-app-type') || '';
            const appTab = col.closest('[data-tab-content]')?.getAttribute('data-tab-content') || '';

            const matchesSearch = !searchTerm ||
                appId.toLowerCase().includes(searchTerm) ||
                appType.toLowerCase().includes(searchTerm);

            const matchesStatus = !statusFilter || appTab === statusFilter;

            col.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    searchInput.addEventListener('keyup', filterApplications);
    statusFilter.addEventListener('change', filterApplications);
});
</script>
@endpush
@endsection
