<!-- SIDEBAR -->
<div id="beneficiarySidebar"
     class="d-flex flex-column bg-white shadow-sm p-3 position-fixed top-0 start-0 vh-100 border-end sidebar-expanded"
     style="width: 270px; z-index: 1040; transition: width 0.3s;">

     <!-- Close button for mobile -->
    <button id="sidebarCloseBtn" class="btn btn-light d-lg-none position-absolute top-0 end-0 m-2">
        <i class="bi bi-x-lg"></i>
    </button>

    <div class="mb-4 text-center pb-3 border-bottom sidebar-header">
        <span class="fw-bold mb-0 text-primary sidebar-hide" style="font-size:1.1rem;">PWD & Senior Aid Portal</span>
    </div>

    <!-- Profile -->
    <div class="d-flex flex-column align-items-center mb-4 p-2 bg-light rounded sidebar-profile">
        <img src="{{ asset('img/profile.png') }}" class="rounded-circle border" style="width:48px; height:48px; object-fit:cover;">
        <div class="flex-grow-1 text-center sidebar-hide">
            <p class="mb-0 fw-semibold text-dark">
                {{ Auth::guard('beneficiary')->user()->first_name }} {{ Auth::guard('beneficiary')->user()->last_name }}
            </p>
            <small class="text-muted d-block">
                @if(Auth::guard('beneficiary')->user()->beneficiary_type === 'PWD')
                    PWD - ID: {{ Auth::guard('beneficiary')->user()->pwd_id }}
                @elseif(Auth::guard('beneficiary')->user()->beneficiary_type === 'Senior Citizen')
                    Senior - ID: {{ Auth::guard('beneficiary')->user()->osca_number }}
                @else
                    Beneficiary
                @endif
            </small>
        </div>
    </div>

    <hr class="my-2 sidebar-hide">

    <!-- Dashboard -->
    <nav class="nav flex-column gap-1">
        <a href="{{ route('beneficiaries.dashboard') }}"
           class="nav-link d-flex align-items-center active bg-primary text-white rounded px-3 py-2"
           data-bs-toggle="tooltip" title="Dashboard">
            <i class="bi bi-speedometer2 me-2"></i>
            <span class="sidebar-hide">Dashboard</span>
        </a>
    </nav>

    <hr class="my-3 sidebar-hide">

    <!-- Account -->
    <div>
        <small class="text-uppercase text-muted fw-semibold mb-1 d-block px-3 sidebar-hide">Account</small>
        <a href="{{ route('beneficiaries.profile') }}"
           class="nav-link d-flex align-items-center px-3 py-2 text-dark rounded hover-bg-light"
           data-bs-toggle="tooltip" title="Profile Management">
            <i class="bi bi-person-circle me-2"></i>
            <span class="sidebar-hide">Profile Management</span>
        </a>
    </div>

    <hr class="my-3 sidebar-hide">

    <!-- Services -->
    <div>
        <small class="text-uppercase text-muted fw-semibold mb-1 d-block px-3 sidebar-hide">Services</small>
        <a href="{{ route('beneficiaries.documents') }}"
           class="nav-link d-flex align-items-center px-3 py-2 text-dark rounded hover-bg-light"
           data-bs-toggle="tooltip" title="Documents">
            <i class="bi bi-folder2-open me-2"></i>
            <span class="sidebar-hide">Documents</span>
        </a>
        <a href="{{ route('beneficiaries.applications') }}"
           class="nav-link d-flex align-items-center px-3 py-2 text-dark rounded hover-bg-light"
           data-bs-toggle="tooltip" title="Applications">
            <i class="bi bi-file-earmark-text me-2"></i>
            <span class="sidebar-hide">Applications</span>
        </a>
    </div>

    <hr class="my-3 sidebar-hide">

    <!-- Communication -->
    <div>
        <small class="text-uppercase text-muted fw-semibold mb-1 d-block px-3 sidebar-hide">Communication</small>
        <a href="#" class="nav-link d-flex align-items-center px-3 py-2 text-dark rounded hover-bg-light"
           data-bs-toggle="tooltip" title="Notifications">
            <i class="bi bi-bell me-2"></i>
            <span class="sidebar-hide">Notifications</span>
            <span class="badge bg-danger ms-auto sidebar-hide">2</span>
        </a>
    </div>

    <hr class="my-3 sidebar-hide">

    <!-- Footer / Logout -->
    <div class="mt-auto">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2"
                    data-bs-toggle="tooltip" title="Sign Out">
                <i class="bi bi-box-arrow-right"></i>
                <span class="sidebar-hide">Sign Out</span>
            </button>
        </form>
    </div>
</div>
