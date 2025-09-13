document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('brgy-sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const darkToggle = document.getElementById('dark-toggle');

    // Sidebar toggle
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }

    // Highlight active link
    const currentPath = window.location.pathname;
    document.querySelectorAll('#brgy-sidebar a').forEach(link => {
        if (link.href.includes(currentPath)) {
            link.classList.add('active');
        }
    });

    // Dark mode toggle
    if (darkToggle) {
        darkToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark');
        });
    }
});
