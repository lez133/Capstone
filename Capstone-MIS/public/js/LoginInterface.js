document.addEventListener("DOMContentLoaded", function () {
    const modalElement = document.getElementById("loginModal");
    const hasLoginError = document.body.dataset.loginError === "true";

    // Attach password toggle once
    function attachPasswordToggle() {
        const togglePassword = document.getElementById("togglePassword");
        const passwordField = document.getElementById("password");
        if (!togglePassword || !passwordField) return;

        togglePassword.addEventListener("click", function () {
            const icon = this.querySelector("i");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                if (icon) icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                passwordField.type = "password";
                if (icon) icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        });
    }

    // Initialize only one Bootstrap modal instance
    if (modalElement) {
        const loginModal = bootstrap.Modal.getOrCreateInstance(modalElement);

        // Attach password toggle when modal is shown
        modalElement.addEventListener("shown.bs.modal", () => {
            attachPasswordToggle();
            const usernameField = modalElement.querySelector("#username");
            if (usernameField) usernameField.focus();
        });

        // Clean up body and backdrop when closed (prevents freeze)
        modalElement.addEventListener("hidden.bs.modal", () => {
            document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
            document.body.classList.remove("modal-open");
            document.body.style.removeProperty("overflow");
            document.body.style.removeProperty("padding-right");
        });

        // Auto show modal if login error
        if (hasLoginError) {
            setTimeout(() => loginModal.show(), 150);
        }
    }
});
