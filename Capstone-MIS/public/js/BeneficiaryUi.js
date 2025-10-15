document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('beneficiarySidebar');
    const collapseBtn = document.getElementById('sidebarCollapseBtn');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
    const collapseIcon = document.getElementById('sidebarCollapseIcon');
    const mainContent = document.getElementById('mainContent');
    const overlay = document.getElementById('sidebarOverlay');

    // Toggle sidebar collapse / expand
    collapseBtn.addEventListener('click', function () {
        sidebar.classList.toggle('sidebar-collapsed');
        sidebar.classList.toggle('sidebar-expanded');

        if (sidebar.classList.contains('sidebar-collapsed')) {
            collapseIcon.classList.remove('bi-x');
            collapseIcon.classList.add('bi-list');

            // Desktop behavior
            if (window.innerWidth > 991 && mainContent) {
                mainContent.style.marginLeft = '60px';
            }

            // Mobile behavior
            if (window.innerWidth <= 991) {
                sidebar.style.transform = 'translateX(-100%)';
                overlay.classList.remove('active');
            }
        } else {
            collapseIcon.classList.remove('bi-list');
            collapseIcon.classList.add('bi-x');

            // Desktop behavior
            if (window.innerWidth > 991 && mainContent) {
                mainContent.style.marginLeft = '260px';
            }

            // Mobile behavior
            if (window.innerWidth <= 991) {
                sidebar.style.transform = 'translateX(0)';
                overlay.classList.add('active');
            }
        }
    });

    // Mobile close button
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', function () {
            sidebar.classList.add('sidebar-collapsed');
            sidebar.classList.remove('sidebar-expanded');
            collapseIcon.classList.remove('bi-x');
            collapseIcon.classList.add('bi-list');

            if (window.innerWidth <= 991) {
                sidebar.style.transform = 'translateX(-100%)';
                overlay.classList.remove('active');
            }
        });
    }

    // Overlay click closes sidebar on mobile
    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.add('sidebar-collapsed');
            sidebar.classList.remove('sidebar-expanded');
            collapseIcon.classList.remove('bi-x');
            collapseIcon.classList.add('bi-list');

            sidebar.style.transform = 'translateX(-100%)';
            overlay.classList.remove('active');
        });
    }

    // Ensure proper layout on window resize
    window.addEventListener('resize', function () {
        if (window.innerWidth > 991) {
            sidebar.style.transform = '';
            overlay.classList.remove('active');
            if (sidebar.classList.contains('sidebar-expanded') && mainContent) {
                mainContent.style.marginLeft = '260px';
            } else if (mainContent) {
                mainContent.style.marginLeft = '60px';
            }
        } else {
            if (sidebar.classList.contains('sidebar-expanded')) {
                sidebar.style.transform = 'translateX(0)';
                overlay.classList.add('active');
            } else {
                sidebar.style.transform = 'translateX(-100%)';
                overlay.classList.remove('active');
            }
            if (mainContent) {
                mainContent.style.marginLeft = '0';
            }
        }
    });
});
