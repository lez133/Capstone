document.addEventListener("DOMContentLoaded", function () {
    const step1Tab = document.getElementById("step1-tab");
    const step2Tab = document.getElementById("step2-tab");
    const step3Tab = document.getElementById("step3-tab");

    const showPasswordCheckbox = document.getElementById("showPassword");
    const passwordField = document.getElementById("password");
    const confirmPasswordField = document.getElementById("password_confirmation");

    function showModal(message) {
        document.getElementById("modalMessage").innerText = message;
        const modal = new bootstrap.Modal(document.getElementById("messageModal"));
        modal.show();
    }

    // Step 1 -> Step 2
    document.getElementById("next-step1").addEventListener("click", () => {
        const errors = validateStep1();
        if (errors.length) {
            showModal(errors.join("\n")); // Show errors in the modal
            return;
        }
        step2Tab.disabled = false;
        step2Tab.click();
    });

    // Step 2 -> Step 3
    document.getElementById("next-step2").addEventListener("click", async () => {
        const nextBtn = document.getElementById("next-step2");
        nextBtn.disabled = true; // Disable button during validation

        const errors = validateStep2();
        if (errors.length) {
            showModal(errors.join("\n"));
            nextBtn.disabled = false;
            return;
        }

        const contact = document.getElementById("contact").value.trim();
        const validContact = await checkUnique("contact", contact);
        if (!validContact) {
            showModal("Contact number is already taken.");
            nextBtn.disabled = false;
            return;
        }

        const email = document.getElementById("email").value.trim();
        const validEmail = await checkUnique("email", email);
        if (!validEmail) {
            showModal("Email is already taken.");
            nextBtn.disabled = false;
            return;
        }

        step3Tab.disabled = false;
        step3Tab.click();
        nextBtn.disabled = false; // Re-enable after success
    });

    // Previous buttons
    document.getElementById("prev-step2").addEventListener("click", () => step1Tab.click());
    document.getElementById("prev-step3").addEventListener("click", () => step2Tab.click());

    // Show/Hide password
    showPasswordCheckbox.addEventListener("change", () => {
        const type = showPasswordCheckbox.checked ? "text" : "password";
        passwordField.type = type;
        confirmPasswordField.type = type;
    });

    // Final submit check
    document.getElementById("addMemberForm").addEventListener("submit", async (e) => {
        e.preventDefault(); // Prevent the form from submitting immediately

        const username = document.getElementById("username").value.trim();
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;

        // Validate password strength
        const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        if (!passRegex.test(password)) {
            return showModal("Password must be at least 8 characters long, include uppercase, lowercase, and a number.");
        }

        // Validate password confirmation
        if (password !== confirmPassword) {
            return showModal("Passwords do not match.");
        }

        // Check if the username is unique
        const validUser = await checkUnique("username", username);
        if (!validUser) {
            return showModal("Username is already taken.");
        }

        // If all validations pass, submit the form
        e.target.submit();
    });

    // Helpers
    function validateStep1() {
        const errors = [];

        // Validate first name
        if (!document.getElementById("fname").value.trim()) {
            errors.push("First name is required.");
        }

        // Validate last name
        if (!document.getElementById("lname").value.trim()) {
            errors.push("Last name is required.");
        }

        // Validate gender
        if (!document.getElementById("gender").value) {
            errors.push("Gender is required.");
        }

        // Validate birthdate fields
        const day = parseInt(document.getElementById("birth_day").value.trim());
        const month = parseInt(document.getElementById("birth_month").value.trim());
        const year = parseInt(document.getElementById("birth_year").value.trim());

        if (!day || day < 1 || day > 31) {
            errors.push("Valid birth day is required.");
        }
        if (!month || month < 1 || month > 12) {
            errors.push("Valid birth month is required.");
        }
        if (!year || year < 1900 || year > new Date().getFullYear()) {
            errors.push("Valid birth year is required.");
        }

        // Validate if the date is valid
        if (day && month && year && !isValidDate(year, month, day)) {
            errors.push("The selected birthdate is invalid.");
        }

        return errors;
    }

    // Helper function to check if a date is valid
    function isValidDate(year, month, day) {
        const date = new Date(year, month - 1, day); // Month is 0-indexed in JavaScript
        return (
            date.getFullYear() === year &&
            date.getMonth() === month - 1 &&
            date.getDate() === day
        );
    }

    function validateStep2() {
        const errors = [];
        let contact = document.getElementById("contact").value.trim();
        const email = document.getElementById("email").value.trim();

        // Force 639 format if entered as 09XXXXXXXXX
        if (/^09\d{9}$/.test(contact)) {
            contact = "63" + contact.substring(1);
            document.getElementById("contact").value = contact;
        }

        // Validate PH number format (only 639XXXXXXXXX allowed after conversion)
        if (!/^639\d{9}$/.test(contact)) {
            errors.push("Contact number must be a valid PH mobile number (639XXXXXXXXX).");
        }

        // Email is optional, but must be valid if provided
        if (email && !/^[\w\.-]+@[\w\.-]+\.\w{2,}$/.test(email)) {
            errors.push("Email must be a valid email address.");
        }

        return errors;
    }

    async function checkUnique(field, value) {
        try {
            const res = await fetch("/admin/validate-member-field", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({ field, value }),
            });

            const text = await res.text(); // Read response as text first (debug-safe)

            try {
                const data = JSON.parse(text); // Try to parse JSON
                return data.valid;
            } catch (jsonErr) {
                console.error("Invalid JSON response from server:", text);
                return false;
            }
        } catch (err) {
            console.error("Error in checkUnique:", err);
            return false;
        }
    }

});
