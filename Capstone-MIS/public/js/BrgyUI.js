document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('brgy-sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const closeBtn = document.getElementById('sidebar-close');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');
    const darkToggle = document.getElementById('dark-toggle');
    const mainContent = document.getElementById('main-content');

    // === Restore Sidebar State ===
    const savedState = localStorage.getItem('sidebarState');
    if (savedState === 'collapsed') {
        sidebar.classList.add('collapsed');
        mainContent.style.marginLeft = '70px';
    } else {
        mainContent.style.marginLeft = '250px';
    }

    // === TOGGLE SIDEBAR ===
    function toggleSidebar() {
        if (window.innerWidth < 992) {
            // Mobile behavior
            sidebar.classList.toggle('active');
            sidebarBackdrop.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        } else {
            // Desktop collapse
            sidebar.classList.toggle('collapsed');
            const collapsed = sidebar.classList.contains('collapsed');
            mainContent.style.marginLeft = collapsed ? '70px' : '250px';
            localStorage.setItem('sidebarState', collapsed ? 'collapsed' : 'expanded');
        }
    }

    function closeSidebar() {
        sidebar.classList.remove('active');
        sidebarBackdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeSidebar);

    // === Highlight Active Nav ===
    const currentPathRaw = window.location.pathname || '/';
    const normalize = p => (p.endsWith('/') && p !== '/') ? p.slice(0, -1) : p;
    const currentPath = normalize(currentPathRaw);
    document.querySelectorAll('#brgy-sidebar a').forEach(link => {
        try {
            const linkPath = normalize(new URL(link.href).pathname || '/');
            if (linkPath === currentPath) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        } catch (e) {
            // fallback: ignore malformed href
        }
    });

    // === Dark Mode ===
    const savedTheme = localStorage.getItem('darkMode');
    if (savedTheme === 'enabled') document.body.classList.add('dark');

    if (darkToggle) {
        darkToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark');
            localStorage.setItem('darkMode', document.body.classList.contains('dark') ? 'enabled' : 'disabled');
        });
    }
});
