document.addEventListener("DOMContentLoaded", function () {
    // ------------------ STEP NAVIGATION ------------------
    function goToStep(stepNumber) {
        document.querySelectorAll(".step").forEach(s => s.classList.add("d-none"));
        const el = document.getElementById(`step${stepNumber}`);
        if (el) el.classList.remove("d-none");
        updateStepper(stepNumber);
    }

    window.nextStep = goToStep;
    window.prevStep = goToStep;

    function updateStepper(stepNumber) {
        for (let i = 1; i <= 4; i++) {
            const indicator = document.getElementById(`stepIndicator${i}`);
            if (!indicator) continue;
            if (i < stepNumber) {
                indicator.className = "rounded-circle bg-success text-white d-flex align-items-center justify-content-center mx-auto";
            } else if (i === stepNumber) {
                indicator.className = "rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto";
            } else {
                indicator.className = "rounded-circle bg-light text-dark border d-flex align-items-center justify-content-center mx-auto";
            }
        }
        const progress = document.getElementById("progressLine");
        if (progress) progress.style.width = ((stepNumber - 1) / 3) * 100 + "%";
    }

    let warningModalInstance = null;

    function showWarning(message) {
        const el = document.getElementById("warningMessage");
        if (el) el.innerHTML = message;
        const modalEl = document.getElementById("warningModal");
        if (modalEl) {
            if (!warningModalInstance) {
                warningModalInstance = new bootstrap.Modal(modalEl);
            }
            warningModalInstance.show();
        } else {
            alert(message);
        }
    }

    // ------------------ UNIQUE CHECK ------------------
    async function checkUnique(field, value) {
        if (!value) return true;
        const csrf = document.querySelector('input[name="_token"]').value;
        try {
            const res = await fetch("/assist-registration/validate-field", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf,
                },
                body: JSON.stringify({ field, value }),
            });
            if (!res.ok) throw new Error("Server error");
            const data = await res.json();
            const errorEl = document.getElementById(field + "-error");
            if (!data.valid) {
                if (errorEl) errorEl.innerText = data.message || (field + " is already taken.");
                showWarning(data.message || (field + " is already taken."));
                return false;
            } else {
                if (errorEl) errorEl.innerText = "";
                return true;
            }
        } catch (err) {
            showWarning("Unable to validate " + field + ". Please try again.", field);
            return false;
        }
    }

    // ------------------ STEP VALIDATIONS ------------------
    window.validateStep1 = async function () {
        const lastName = document.getElementById("last_name").value.trim();
        const firstName = document.getElementById("first_name").value.trim();
        const email = document.getElementById("email").value.trim();
        let phoneRaw = document.getElementById("phone").value.trim();

        let errors = [];
        if (!lastName) errors.push("Last name is required.");
        if (!firstName) errors.push("First name is required.");
        if (!phoneRaw) errors.push("Phone number is required.");

        // Normalize phone to 639XXXXXXXXX if possible
        let phone = phoneRaw;
        if (phoneRaw && /^09\d{9}$/.test(phoneRaw)) {
            phone = "63" + phoneRaw.substring(1);
        }

        // Validate format early
        if (phone && !/^(639\d{9})$/.test(phone)) {
            errors.push("Phone number must be in 639XXXXXXXXX format (or 09XXXXXXXXX).");
        }

        if (errors.length) return showWarning(errors.join("<br>"));

        // Check unique phone
        const phoneValid = await checkUnique("phone", phone);
        if (!phoneValid) return;

        // Ensure the input value is normalized for submission
        document.getElementById("phone").value = phone;

        // Only check unique email if provided
        if (email) {
            const emailValid = await checkUnique("email", email);
            if (!emailValid) return;
        }

        nextStep(2);
    };

    window.validateStep2 = function () {
        const type = document.getElementById("beneficiary_type").value;
        const birthday = document.getElementById("birthday").value;
        const gender = document.getElementById("gender").value;
        const civil = document.getElementById("civil_status").value;

        let errors = [];
        if (!type) errors.push("Beneficiary type is required.");
        if (!birthday) errors.push("Birthday is required.");
        if (!gender) errors.push("Gender is required.");
        if (!civil) errors.push("Civil status is required.");

        // Calculate age
        if (birthday) {
            const birthDate = new Date(birthday);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            document.getElementById("age").value = age;

            // Validate age for Senior Citizen
            if (type === "Senior Citizen" && age < 60) {
                errors.push("Senior Citizens must be 60 years old or older.");
            }
        }

        if (errors.length) return showWarning(errors.join("<br>"));

        // Show/hide ID fields based on beneficiary type
        document.querySelectorAll(".senior-only").forEach(el => el.classList.add("d-none"));
        document.querySelectorAll(".pwd-only").forEach(el => el.classList.add("d-none"));
        if (type === "Senior Citizen") {
            document.querySelectorAll(".senior-only").forEach(el => el.classList.remove("d-none"));
        } else if (type === "PWD") {
            document.querySelectorAll(".pwd-only").forEach(el => el.classList.remove("d-none"));
        } else if (type === "Both") {
            document.querySelectorAll(".senior-only").forEach(el => el.classList.remove("d-none"));
            document.querySelectorAll(".pwd-only").forEach(el => el.classList.remove("d-none"));
        }

        nextStep(3);
    };

    window.validateStep3 = function () {
        const type = document.getElementById("beneficiary_type").value;
        const osca = document.getElementById("osca_number").value.trim();
        const pwd = document.getElementById("pwd_id").value.trim();

        let errors = [];
        if (type === "Senior Citizen" && !osca) errors.push("OSCA number is required.");
        if (type === "PWD" && !pwd) errors.push("PWD ID is required.");
        if (type === "Both" && (!osca || !pwd)) errors.push("Both OSCA number and PWD ID are required.");

        if (errors.length) return showWarning(errors.join("<br>"));

        // Fill summary
        document.getElementById("summary_name").innerText =
            document.getElementById("first_name").value + " " + document.getElementById("last_name").value;
        document.getElementById("summary_email").innerText = document.getElementById("email").value;
        document.getElementById("summary_phone").innerText = document.getElementById("phone").value;
        document.getElementById("summary_type").innerText = type;
        document.getElementById("summary_birthday").innerText = document.getElementById("birthday").value;
        document.getElementById("summary_age").innerText = document.getElementById("age").value;
        document.getElementById("summary_gender").innerText = document.getElementById("gender").value;
        document.getElementById("summary_civil_status").innerText = document.getElementById("civil_status").value;
        if (osca) document.getElementById("summary_osca").innerText = osca;
        if (pwd) document.getElementById("summary_pwd").innerText = pwd;

        nextStep(4);
    };

    window.validateStep4 = async function () {
        const username = document.getElementById("username").value.trim();
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("password_confirmation").value;

        let errors = [];
        if (!username) errors.push("Username is required.");
        if (!password) errors.push("Password is required.");
        if (!confirmPassword) errors.push("Confirm password is required.");

        const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        if (password && !passRegex.test(password)) {
            errors.push("Password must be at least 8 characters and include uppercase, lowercase, and number.");
        }
        if (password !== confirmPassword) errors.push("Passwords do not match.");

        if (errors.length) return showWarning(errors.join("<br>"));

        const userValid = await checkUnique("username", username);
        if (!userValid) return;

        document.getElementById("assistRegistrationForm").submit();
    };

    // ------------------ PASSWORD TOGGLE ------------------
    document.querySelectorAll(".toggle-password").forEach(btn => {
        btn.addEventListener("click", function () {
            const target = document.querySelector(this.getAttribute("data-target"));
            if (!target) return;
            if (target.type === "password") {
                target.type = "text";
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                target.type = "password";
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });

    // ------------------ LIVE VALIDATION ------------------
    const emailInput = document.getElementById("email");
    if (emailInput) {
        emailInput.addEventListener("blur", () => checkUnique("email", emailInput.value));
    }
    const usernameInput = document.getElementById("username");
    if (usernameInput) {
        usernameInput.addEventListener("blur", () => checkUnique("username", usernameInput.value));
    }
    const phoneInput = document.getElementById("phone");
    if (phoneInput) {
        phoneInput.addEventListener("blur", async () => {
            let phone = phoneInput.value.trim();
            // Auto-convert 09XXXXXXXXX to 639XXXXXXXXX
            if (/^09\d{9}$/.test(phone)) {
                phone = "63" + phone.substring(1);
                phoneInput.value = phone;
            }
            await checkUnique("phone", phone);
        });
    }

    // Start on step 1
    nextStep(1);

    // Auto-calculate age when the birthday field changes
    document.getElementById("birthday")?.addEventListener("change", function () {
        const birthday = this.value;
        if (birthday) {
            const birthDate = new Date(birthday);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            document.getElementById("age").value = age;
        }
    });
});
