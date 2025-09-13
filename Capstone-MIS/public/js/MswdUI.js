document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('mswd-sidebar');
    const toggle = document.getElementById('sidebar-toggle');

    toggle?.addEventListener('click', () => {
        if (window.innerWidth < 992) {
            sidebar?.classList.toggle('show');
        } else {
            sidebar?.classList.toggle('collapsed');
        }
    });

    document.addEventListener('click', (e) => {
        if (window.innerWidth < 992) {
            if (sidebar && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });

    const showPasswordCheckbox = document.getElementById('showPassword');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('password_confirmation');

    showPasswordCheckbox.addEventListener('change', () => {
        const type = showPasswordCheckbox.checked ? 'text' : 'password';
        passwordField.type = type;
        confirmPasswordField.type = type;
    });
});
