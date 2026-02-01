document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('beneficiarySidebar');
    const collapseBtn = document.getElementById('sidebarCollapseBtn');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
    const collapseIcon = document.getElementById('sidebarCollapseIcon');
    const overlay = document.getElementById('sidebarOverlay');
    const mainLayout = document.getElementById('mainLayout');

    const isMobile = () => window.innerWidth <= 991;

    function collapseSidebar() {
        sidebar.classList.add('sidebar-collapsed');
        sidebar.classList.remove('sidebar-expanded');
        collapseIcon.classList.remove('bi-x');
        collapseIcon.classList.add('bi-list');
        if (isMobile()) {
            sidebar.style.transform = 'translateX(-100%)';
            overlay.classList.remove('active');
        } else {
            mainLayout.style.marginLeft = '60px';
            document.querySelector('.beneficiary-header').style.left = '60px';
        }
    }

    function expandSidebar() {
        sidebar.classList.remove('sidebar-collapsed');
        sidebar.classList.add('sidebar-expanded');
        collapseIcon.classList.remove('bi-list');
        collapseIcon.classList.add('bi-x');
        if (isMobile()) {
            sidebar.style.transform = 'translateX(0)';
            overlay.classList.add('active');
        } else {
            mainLayout.style.marginLeft = '270px';
            document.querySelector('.beneficiary-header').style.left = '270px';
        }
    }

    collapseBtn.addEventListener('click', function () {
        if (sidebar.classList.contains('sidebar-expanded')) collapseSidebar();
        else expandSidebar();
    });

    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', collapseSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', collapseSidebar);
    }

    window.addEventListener('resize', function () {
        if (isMobile()) {
            mainLayout.style.marginLeft = '0';
            document.querySelector('.beneficiary-header').style.left = '0';
            if (sidebar.classList.contains('sidebar-expanded')) {
                sidebar.style.transform = 'translateX(0)';
                overlay.classList.add('active');
            } else {
                sidebar.style.transform = 'translateX(-100%)';
                overlay.classList.remove('active');
            }
        } else {
            sidebar.style.transform = '';
            overlay.classList.remove('active');
            if (sidebar.classList.contains('sidebar-expanded')) {
                mainLayout.style.marginLeft = '270px';
                document.querySelector('.beneficiary-header').style.left = '270px';
            } else {
                mainLayout.style.marginLeft = '60px';
                document.querySelector('.beneficiary-header').style.left = '60px';
            }
        }
    });

    // Resubmit button handler: prefill and open the submit modal
    document.querySelectorAll('.resubmit-btn').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            const docType = btn.getAttribute('data-document-type') || '';
            const typeInput = document.getElementById('document_type');
            if (typeInput) typeInput.value = docType;

            // open Bootstrap modal programmatically (Bootstrap 5)
            const modalEl = document.getElementById('submitDocumentModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } else {
                // fallback: if data-bs-toggle present on modal trigger, try clicking hidden trigger
                const trigger = document.querySelector('[data-bs-target="#submitDocumentModal"]');
                if (trigger) trigger.click();
            }
        });
    });
});
