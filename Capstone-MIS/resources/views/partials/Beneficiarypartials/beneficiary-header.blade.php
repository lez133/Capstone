<div class="beneficiary-header d-flex justify-content-between align-items-center bg-white shadow-sm px-4 py-3">
    <div class="d-flex align-items-center gap-2">
        <!-- Sidebar Toggle Button -->
        <button id="sidebarCollapseBtn" class="btn btn-light border rounded-circle me-2">
            <i class="bi bi-list" id="sidebarCollapseIcon"></i>
        </button>
        <h5 class="mb-0 fw-bold">Dashboard</h5>
    </div>
    <!-- Notification Bell Redirects to Notification Tab -->
    <a href="{{ route('beneficiary.notifications.index') }}" class="btn btn-light border rounded-circle" title="Notifications">
        <i class="bi bi-bell"></i>
    </a>
</div>
