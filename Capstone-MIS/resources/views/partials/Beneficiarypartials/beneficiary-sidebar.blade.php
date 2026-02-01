<!-- SIDEBAR -->
<div id="beneficiarySidebar"
     class="d-flex flex-column bg-white p-3 sidebar-expanded shadow-sm">

    <!-- Close button for mobile -->
    <button id="sidebarCloseBtn" class="btn btn-light d-lg-none position-absolute top-0 end-0 m-2">
        <i class="bi bi-x-lg"></i>
    </button>

    <!-- Header -->
    <div class="mb-4 text-center pb-3 border-bottom sidebar-header">
        @php
            $bsUser = Auth::guard('beneficiary')->user();
            $headerTitle = 'Aid Portal';
            if ($bsUser) {
                $type = strtolower(trim($bsUser->beneficiary_type ?? ''));
                if ($type === 'pwd') {
                    $headerTitle = 'PWD Aid Portal';
                } elseif ($type === 'senior citizen' || $type === 'senior') {
                    $headerTitle = 'Senior Citizen Aid Portal';
                } else {
                    $headerTitle = 'Beneficiary Aid Portal';
                }
            }
        @endphp
        <span class="fw-bold mb-0 text-primary sidebar-hide" style="font-size:1.1rem;">{{ $headerTitle }}</span>
    </div>

    <!-- Profile -->
    <div class="d-flex flex-column align-items-center mb-4 p-3 bg-light rounded sidebar-profile">
        @php
            $bsUser = Auth::guard('beneficiary')->user();
            // Use uploaded avatar if present (stored in storage/), otherwise fall back to default image
            $avatarUrl = asset('img/bene-default-bg.png');
            if ($bsUser && !empty($bsUser->avatar)) {
                $avatarUrl = asset('storage/' . ltrim($bsUser->avatar, '/'));
            }
        @endphp

        <img src="{{ $avatarUrl }}"
             class="rounded-circle border sidebar-icon"
             style="width:64px; height:64px; object-fit:cover;">

        <div class="flex-grow-1 text-center sidebar-hide mt-2">
            <p class="mb-0 fw-semibold text-dark" style="font-size:1.05rem;">
                {{ $bsUser ? ($bsUser->first_name . ' ' . $bsUser->last_name) : '' }}
            </p>
            <small class="text-muted d-block" style="font-size:0.9rem;">
                @if($bsUser && strtolower($bsUser->beneficiary_type) === 'pwd')
                    PWD - ID: {{ $bsUser->pwd_id }}
                @elseif($bsUser && (strtolower($bsUser->beneficiary_type) === 'senior citizen' || strtolower($bsUser->beneficiary_type) === 'senior'))
                    Senior - ID: {{ $bsUser->osca_number }}
                @else
                    Beneficiary
                @endif
            </small>
        </div>
    </div>

    <hr class="my-2 sidebar-hide">

    <nav class="nav flex-column gap-1">
        <a href="{{ route('beneficiaries.dashboard') }}"
           class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 sidebar-item {{ request()->routeIs('beneficiaries.dashboard') ? 'active bg-primary text-white' : 'text-dark hover-bg-light' }}"
           data-bs-toggle="tooltip" title="Dashboard" {{ request()->routeIs('beneficiaries.dashboard') ? 'aria-current=page' : '' }}>
            <i class="bi bi-speedometer2 sidebar-icon"></i>
            <span class="sidebar-hide">Dashboard</span>
        </a>

        <hr class="my-3 sidebar-hide">

        <small class="text-uppercase text-muted fw-semibold mb-1 d-block px-3 sidebar-hide">Account</small>
        <a href="{{ route('beneficiaries.profile') }}"
           class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 sidebar-item {{ request()->routeIs('beneficiaries.profile') ? 'active bg-primary text-white' : 'text-dark hover-bg-light' }}"
           data-bs-toggle="tooltip" title="Profile Management" {{ request()->routeIs('beneficiaries.profile') ? 'aria-current=page' : '' }}>
            <i class="bi bi-person-circle sidebar-icon"></i>
            <span class="sidebar-hide">Profile Management</span>
        </a>

        <hr class="my-3 sidebar-hide">

        <small class="text-uppercase text-muted fw-semibold mb-1 d-block px-3 sidebar-hide">Services</small>
        @php
            $user = Auth::guard('beneficiary')->user();
            $isSenior = strtolower($user->beneficiary_type) === 'senior citizen';
            $isPWD70 = (strtolower($user->beneficiary_type) === 'pwd') && (
                $user->birthday && \Carbon\Carbon::parse($user->birthday)->age >= 70
            );
        @endphp
        @if($isSenior || $isPWD70)
            <a href="{{ route('beneficiary.centenarian-cash-gift') }}"
               class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 sidebar-item {{ request()->routeIs('beneficiary.centenarian-cash-gift') ? 'active bg-primary text-white' : 'text-dark hover-bg-light' }}"
               data-bs-toggle="tooltip" title="Centenarian Cash Gift" {{ request()->routeIs('beneficiary.centenarian-cash-gift') ? 'aria-current=page' : '' }}>
                <i class="bi bi-gift-fill sidebar-icon"></i>
                <span class="sidebar-hide">Centenarian Cash Gift</span>
            </a>
        @endif

        <a href="{{ route('beneficiaries.documents') }}"
           class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 sidebar-item {{ request()->routeIs('beneficiaries.documents') ? 'active bg-primary text-white' : 'text-dark hover-bg-light' }}"
           data-bs-toggle="tooltip" title="Documents" {{ request()->routeIs('beneficiaries.documents') ? 'aria-current=page' : '' }}>
            <i class="bi bi-folder2-open sidebar-icon"></i>
            <span class="sidebar-hide">Documents</span>
        </a>

        <a href="{{ route('beneficiaries.applications') }}"
           class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 sidebar-item {{ request()->routeIs('beneficiaries.applications') ? 'active bg-primary text-white' : 'text-dark hover-bg-light' }}"
           data-bs-toggle="tooltip" title="Applications" {{ request()->routeIs('beneficiaries.applications') ? 'aria-current=page' : '' }}>
            <i class="bi bi-file-earmark-text sidebar-icon"></i>
            <span class="sidebar-hide">Applications</span>
        </a>

        <hr class="my-3 sidebar-hide">

        <small class="text-uppercase text-muted fw-semibold mb-1 d-block px-3 sidebar-hide">Communication</small>
        @php
            $bsUser = Auth::guard('beneficiary')->user();
            $unreadCount = 0;
            if ($bsUser) {
                // Prefer Laravel polymorphic notifications if table has the expected columns
                if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'notifiable_type') &&
                    \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'notifiable_id')) {
                    $unreadCount = $bsUser->unreadNotifications ? $bsUser->unreadNotifications->count() : 0;
                } else {
                    // Fallbacks for alternate schemas (do not assume read_at exists)
                    $hasReadAt = \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'read_at');
                    if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'beneficiary_id')) {
                        $q = \Illuminate\Support\Facades\DB::table('notifications')->where('beneficiary_id', $bsUser->id);
                        $unreadCount = $hasReadAt ? $q->whereNull('read_at')->count() : $q->count();
                    } elseif (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'user_id')) {
                        $q = \Illuminate\Support\Facades\DB::table('notifications')->where('user_id', $bsUser->id);
                        $unreadCount = $hasReadAt ? $q->whereNull('read_at')->count() : $q->count();
                    } else {
                        $unreadCount = 0;
                    }
                }
            }
        @endphp
        <a href="{{ route('beneficiary.notifications.index') }}"
           class="nav-link d-flex align-items-center gap-2 rounded px-3 py-2 sidebar-item {{ request()->routeIs('beneficiary.notifications.*') ? 'active bg-primary text-white' : 'text-dark hover-bg-light' }}"
           data-bs-toggle="tooltip" title="Notifications" {{ request()->routeIs('beneficiary.notifications.*') ? 'aria-current=page' : '' }}>
            <i class="bi bi-bell sidebar-icon"></i>
            <span class="sidebar-hide">Notifications</span>
            @if($unreadCount > 0)
                <span class="badge bg-danger ms-auto sidebar-hide">{{ $unreadCount }}</span>
            @endif
        </a>

        <hr class="my-3 sidebar-hide">

        <!-- Logout -->
        <form action="{{ route('logout') }}" method="POST" class="mt-auto">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2 sidebar-item"
                    data-bs-toggle="tooltip" title="Sign Out">
                <i class="bi bi-box-arrow-right sidebar-icon"></i>
                <span class="sidebar-hide">Sign Out</span>
            </button>
        </form>
    </nav>
</div>
