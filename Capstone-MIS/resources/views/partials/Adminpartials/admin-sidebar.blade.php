<aside id="mswd-sidebar" class="sidebar">
    <div class="sidebar-header d-flex justify-content-between align-items-center">
        <div class="sidebar-brand d-flex align-items-center">
            <img src="{{ asset('img/admin-icon.png') }}" alt="MSWD" height="36">
            <div class="brand-text ms-2">
                <div class="fw-bold">MSWD ADMIN</div>
                <small class="text-muted">Municipal Dashboard</small>
            </div>
        </div>
        <!-- Close Button for Mobile View -->
        <button class="btn btn-close d-lg-none" id="sidebar-close-btn" aria-label="Close Sidebar"></button>
    </div>

    <!-- Sidebar Content with Scroll -->
    <div class="sidebar-content">
        <nav class="nav flex-column">
            <a href="{{ route('mswd.dashboard') }}" class="nav-link">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
            <a href="{{ route('beneficiaries.index') }}" class="nav-link">
                <i class="fas fa-users me-2"></i> Beneficiaries
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-boxes me-2"></i> Distributions
            </a>
            <a href="#" class="nav-link">
                <i class="fas fa-file-alt me-2"></i> Documents
            </a>
            <a href="{{ route('notifications.index') }}" class="nav-link">
                <i class="fas fa-bell me-2"></i> Notifications
            </a>
            <a href="{{ route('members.index') }}" class="nav-link">
                <i class="fas fa-user-friends me-2"></i> Members
            </a>
            <!-- <a href="#" class="nav-link">
                <i class="fas fa-chart-bar me-2"></i> Reports
            </a> -->
            <a href="{{ route('schedule.index') }}" class="nav-link">
                <i class="fas fa-calendar-alt me-2"></i> Schedule
            </a>
            <a href="{{ route('programs.index') }}" class="nav-link">
                <i class="fas fa-calendar-check me-2"></i> Manage Programs/Events
            </a>
        </nav>

        <!-- <div class="sidebar-actions mt-3">
            <button class="btn btn-sm btn-outline-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modal-register">
                <i class="fas fa-user-plus me-2"></i> Register Beneficiary
            </button>
            <button class="btn btn-sm btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#modal-schedule">
                <i class="fas fa-calendar-alt me-2"></i> Schedule Distribution
            </button>
        </div> -->
    </div>
</aside>
