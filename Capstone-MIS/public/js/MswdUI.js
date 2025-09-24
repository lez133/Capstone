document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('mswd-sidebar');
    const toggle = document.getElementById('sidebar-toggle');
    const closeBtn = document.getElementById('sidebar-close-btn');

    function isMobile() {
        return window.innerWidth < 992;
    }

    // Toggle behavior: desktop -> collapse, mobile -> drawer (show)
    toggle?.addEventListener('click', (e) => {
        e.preventDefault();
        if (isMobile()) {
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open', sidebar.classList.contains('show'));
        } else {
            sidebar.classList.toggle('collapsed');
        }
    });

    // close button in mobile
    closeBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        sidebar.classList.remove('show');
        document.body.classList.remove('sidebar-open');
    });

    // click outside to close on mobile drawer
    document.addEventListener('click', (e) => {
        if (isMobile()) {
            if (sidebar && sidebar.classList.contains('show') &&
                !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        }
    });

    // remove mobile-open state if resizing to desktop
    window.addEventListener('resize', () => {
        if (!isMobile()) {
            sidebar.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
    });

    // Optional: show/hide password fields (guarded)
    const showPasswordCheckbox = document.getElementById('showPassword');
    if (showPasswordCheckbox) {
        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('password_confirmation');
        showPasswordCheckbox.addEventListener('change', () => {
            const type = showPasswordCheckbox.checked ? 'text' : 'password';
            if (passwordField) passwordField.type = type;
            if (confirmPasswordField) confirmPasswordField.type = type;
        });
    }
});
