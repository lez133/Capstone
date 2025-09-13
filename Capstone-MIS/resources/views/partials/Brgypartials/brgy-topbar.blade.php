<header class="topbar d-flex justify-content-between align-items-center shadow-sm">
    <div class="d-flex align-items-center">
        <button id="sidebar-toggle" class="btn btn-outline-secondary me-3">
            <i class="fas fa-bars"></i>
        </button>
        <button id="dark-toggle" class="btn btn-outline-secondary me-3">
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <div class="d-flex align-items-center">
        <!-- Notifications -->
        <div class="notification-icon me-3">
            <i class="fas fa-bell fa-lg text-secondary"></i>
            <span class="notification-badge">3</span>
        </div>

        <!-- User Dropdown -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
                <img src="{{ asset('img/brgy-user-icon.png') }}" width="36" height="36" class="rounded-circle me-2">
                <span class="fw-semibold">Brgy Rep</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#">Profile</a></li>
                <li><a class="dropdown-item" href="#">Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
