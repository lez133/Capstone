<aside id="mswd-sidebar" class="sidebar">
    <style>
        @keyframes mswd-bob {
            0% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
            100% { transform: translateY(0); }
        }

        /* Pause any icon animation from libraries (e.g. FontAwesome) by default */
        .sidebar-content .nav-link i,
        .sidebar-content .nav-link.active i {
            display: inline-block;
            transition: transform .25s ease, color .25s ease;
            will-change: transform;
            animation-play-state: paused !important;
        }

        /* Start icon animation only when hovered or focused (keyboard accessible) */
        .sidebar-content .nav-link:hover i,
        .sidebar-content .nav-link:focus i {
            transform: translateY(-4px) rotate(-8deg);
            color: #0d6efd; /* Bootstrap primary */
            animation-play-state: running !important;
        }

        /* If you keep the custom bob animation for active links, keep it defined but paused by default */
        .sidebar-content .nav-link.active i {
            animation: mswd-bob 3s ease-in-out infinite;
            /* remain paused until hovered */
            animation-play-state: paused !important;
        }

        /* small focus transform for keyboard users */
        .sidebar-content .nav-link:focus i {
            transform: translateY(-2px);
            animation-play-state: running !important;
        }
    </style>

    <div class="sidebar-header d-flex justify-content-between align-items-center">
        <div class="sidebar-brand d-flex align-items-center">
            <img src="{{ asset('img/admin-icon.png') }}" alt="MSWD" height="36" class="moving-icon">
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
                <i class="fas fa-tachometer-alt me-2 fa-beat" aria-hidden="true"></i> Dashboard
            </a>
            <a href="{{ route('beneficiaries.index') }}" class="nav-link">
                <i class="fas fa-users me-2 fa-bounce" aria-hidden="true"></i> Beneficiaries
            </a>
            <a href="{{ route('distribution.barangays') }}" class="nav-link">
                <i class="fas fa-boxes me-2 fa-fade" aria-hidden="true"></i> Distributions
            </a>
            <a href="{{ route('document.barangay.selector') }}" class="nav-link">
                <i class="fas fa-file-alt me-2 fa-shake" aria-hidden="true"></i> Documents
            </a>
            <a href="{{ route('notifications.index') }}" class="nav-link">
                <i class="fas fa-bell me-2 fa-beat-fade" aria-hidden="true"></i> Notifications
            </a>
            <a href="{{ route('members.index') }}" class="nav-link">
                <i class="fas fa-user-friends me-2 fa-flip" aria-hidden="true"></i> Members
            </a>
            <a href="{{ route('schedule.index') }}" class="nav-link">
                <i class="fas fa-calendar-alt me-2 fa-spin" aria-hidden="true"></i> Schedule
            </a>
            <a href="{{ route('programs.index') }}" class="nav-link">
                <i class="fas fa-calendar-check me-2 fa-beat" aria-hidden="true"></i> Manage Programs/Events
            </a>
            <a href="{{ route('reports.index') }}" class="nav-link">
                <i class="fas fa-file-invoice me-2 fa-bounce" aria-hidden="true"></i> Logs & Reports
            </a>
        </nav>
    </div>
</aside>
