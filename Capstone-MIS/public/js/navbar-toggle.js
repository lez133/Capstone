document.addEventListener('DOMContentLoaded', function () {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const rightButtons = document.getElementById('navbarRightButtons');
    const navbarCollapse = document.getElementById('navbarNav');

    function updateButtonsVisibility() {
        if (!navbarCollapse) return; // Ensure navbarCollapse exists before proceeding

        if (window.innerWidth < 992) { // Bootstrap’s lg breakpoint
            if (!navbarCollapse.classList.contains('show')) {
                rightButtons.style.display = 'none';
            } else {
                rightButtons.style.display = '';
            }
        } else {
            rightButtons.style.display = '';
        }
    }

    if (navbarToggler) {
        navbarToggler.addEventListener('click', function () {
            setTimeout(updateButtonsVisibility, 350); // wait for collapse animation
        });
    }

    window.addEventListener('resize', updateButtonsVisibility);
    updateButtonsVisibility();
});
