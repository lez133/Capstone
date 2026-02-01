@php
    $user = auth()->guard('mswd')->user();

    // Profile Picture
    $profilePic = $user && $user->profile_picture
        ? asset('storage/' . $user->profile_picture)
        : asset('img/admin-icon.png');

    // Full Name
    $fullName = $user ? ($user->fname . ' ' . $user->lname) : 'Admin User';
@endphp

<header class="topbar">
    <div class="left">
        <button id="sidebar-toggle" class="btn btn-light btn-sm" aria-label="Toggle sidebar">☰</button>
    </div>

    <div class="right d-flex align-items-center">

        <div class="dropdown">
            <a href="#" id="userDropdown" data-bs-toggle="dropdown" class="d-flex align-items-center text-decoration-none">

                {{-- PROFILE PICTURE --}}
                <img
                    src="{{ $profilePic }}"
                    width="36"
                    height="36"
                    class="rounded-circle me-2"
                    alt="User">

                <div class="d-none d-md-block text-end">
                    <div class="fw-semibold">{{ $fullName }}</div>
                    <small class="text-muted">MSWD</small>
                </div>
            </a>

            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('view-profile.show', Crypt::encrypt($user->id)) }}">
                        Profile
                    </a>
                </li>

                <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}">Change Password</a></li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item" type="submit">Logout</button>
                    </form>
                </li>
            </ul>
        </div>

    </div>
</header>
