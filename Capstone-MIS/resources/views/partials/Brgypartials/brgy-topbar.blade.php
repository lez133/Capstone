{{-- filepath: c:\Lara\Capstone-MIS\resources\views\partials\Brgypartials\brgy-topbar.blade.php --}}
@php
    $user = auth()->guard('brgyrep')->user();
    $profilePic = $user && $user->profile_picture
        ? asset('storage/' . $user->profile_picture)
        : asset('img/brgy-user-icon.png');
    $fullName = $user ? $user->fname . ' ' . $user->lname : 'Brgy Rep';
    $barangayName = $user && $user->barangay ? $user->barangay->barangay_name : '';
@endphp

<header class="topbar d-flex justify-content-between align-items-center shadow-sm px-3 py-2 bg-white">
    <div class="d-flex align-items-center">
        <!-- Sidebar Toggle (Hamburger) -->
        <button id="sidebar-toggle" class="btn btn-outline-secondary me-3 d-flex align-items-center justify-content-center"
                aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Dark Mode -->
        <button id="dark-toggle" class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                aria-label="Toggle dark mode">
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <div class="d-flex align-items-center">

        <!-- Assigned Barangay -->
        @if($barangayName)
            <span class="badge bg-primary me-3">{{ $barangayName }}</span>
        @endif

        <!-- User Dropdown -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">

                <!-- ✅ Dynamic Profile Picture -->
                <img src="{{ $profilePic }}" width="36" height="36" class="rounded-circle me-2" alt="Profile">

                <span class="fw-semibold text-dark">{{ $fullName }}</span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('brgyrep.profile.view', ['encryptedId' => Crypt::encrypt($user->id)]) }}">
                        Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('brgyrep.password.settings', ['encryptedId' => Crypt::encrypt($user->id)]) }}">
                        Change Password
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
