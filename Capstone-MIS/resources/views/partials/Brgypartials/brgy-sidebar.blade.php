<aside id="brgy-sidebar" class="p-3 shadow-sm">
    <div class="d-flex align-items-center mb-4 sidebar-header">
        <img src="{{ asset('img/brgy-user-icon.png') }}" alt="Logo" height="36" class="me-2">
        <span class="fw-bold sidebar-title">Barangay Rep</span>

        <button id="sidebar-close" class="btn btn-link ms-auto d-lg-none text-white" style="font-size:1.5rem;">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="nav flex-column">
        <a href="{{ route('brgyrep.dashboard') }}" class="nav-link {{ request()->routeIs('brgyrep.dashboard') ? 'active' : '' }}"><i class="fas fa-home"></i> <span>Dashboard</span></a>
        <a href="{{ route('assist-registration.create') }}" class="nav-link {{ request()->routeIs('assist-registration.create') ? 'active' : '' }}"><i class="fas fa-user-edit"></i> <span>Assist Registration</span></a>
        <a href="{{ route('brgyrep.submit-document.create') }}" class="nav-link {{ request()->routeIs('brgyrep.submit-document.*') ? 'active' : '' }}"><i class="fas fa-hands-helping"></i> <span>Submit Aid Requests</span></a>
        <a href="{{ route('brgyrep.view-schedules') }}" class="nav-link {{ request()->routeIs('brgyrep.view-schedules') ? 'active' : '' }}"><i class="fas fa-calendar-alt"></i> <span>View Schedules</span></a>
        <a href="{{ route('brgyrep.notifications.interface') }}" class="nav-link {{ request()->routeIs('brgyrep.notifications.*') ? 'active' : '' }}"><i class="fas fa-sms"></i> <span>Monitor SMS Notifications</span></a>
        <a href="{{ route('brgyrep.track-applications.index') }}" class="nav-link {{ request()->routeIs('brgyrep.track-applications.index') ? 'active' : '' }}">
            <i class="fas fa-file-alt"></i> <span>Track Applications</span>
        </a>
    </nav>
</aside>

<!-- Backdrop overlay for mobile -->
<div id="sidebar-backdrop" class="d-lg-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50"
    style="z-index:1039; display:none;"></div>
