document.addEventListener('DOMContentLoaded', function () {
    const showPasswordCheckbox = document.getElementById('showPassword');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('password_confirmation');

    showPasswordCheckbox.addEventListener('change', () => {
        const type = showPasswordCheckbox.checked ? 'text' : 'password';
        passwordField.type = type;
        confirmPasswordField.type = type;
    });
});

document.getElementById('next-step1').addEventListener('click', () => {
    const errors = validateStep1();
    if (errors.length === 0) {
        document.getElementById('step2-tab').disabled = false;
        document.getElementById('step2-tab').click();
    } else {
        showModal(errors.join('<br>')); // Show all errors in the modal
    }
});

document.getElementById('next-step2').addEventListener('click', () => {
    const errors = validateStep2();
    if (errors.length === 0) {
        document.getElementById('step3-tab').disabled = false;
        document.getElementById('step3-tab').click();
    } else {
        showModal(errors.join('<br>')); // Show all errors in the modal
    }
});

document.getElementById('prev-step2').addEventListener('click', () => {
    document.getElementById('step1-tab').click();
});

document.getElementById('prev-step3').addEventListener('click', () => {
    document.getElementById('step2-tab').click();
});

function validateStep1() {
    const errors = [];
    const fname = document.getElementById('fname').value.trim();
    const lname = document.getElementById('lname').value.trim();
    const birthDay = parseInt(document.getElementById('birth_day').value);
    const birthMonth = parseInt(document.getElementById('birth_month').value);
    const birthYear = parseInt(document.getElementById('birth_year').value);
    const gender = document.getElementById('gender').value;

    if (!fname) errors.push('First name is required.');
    if (!lname) errors.push('Last name is required.');
    if (!birthDay || !birthMonth || !birthYear) {
        errors.push('Complete birthday information is required.');
    } else if (!checkDate(birthDay, birthMonth, birthYear)) {
        errors.push('The selected date is invalid. Please enter a valid date.');
    }
    if (!gender) errors.push('Gender is required.');

    return errors;
}

function validateStep2() {
    const errors = [];
    const contact = document.getElementById('contact').value.trim();
    const email = document.getElementById('email').value.trim();
    const contactRegex = /^[0-9]{10,15}$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!contact) {
        errors.push('Contact number is required.');
    } else if (!contactRegex.test(contact)) {
        errors.push('Invalid contact number. Please enter a valid number (10-15 digits).');
    }

    if (!email) {
        errors.push('Email address is required.');
    } else if (!emailRegex.test(email)) {
        errors.push('Invalid email address. Please enter a valid email.');
    }

    return errors;
}

function checkDate(day, month, year) {
    const date = new Date(year, month - 1, day);
    return (
        date.getFullYear() === year &&
        date.getMonth() + 1 === month &&
        date.getDate() === day
    );
}

function showModal(message) {
    const modalMessage = document.getElementById('modalMessage');
    modalMessage.innerHTML = message; // Use innerHTML to support multiple lines

    const messageModal = new bootstrap.Modal(document.getElementById('messageModal'), {
        backdrop: 'static',
        keyboard: true,
    });

    messageModal.show();

    const modalElement = document.getElementById('messageModal');
    modalElement.addEventListener('hidden.bs.modal', () => {
        modalMessage.innerHTML = ''; // Clear the modal message after closing
    });
}

document.getElementById('addMemberForm').addEventListener('submit', (e) => {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('password_confirmation').value;
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

    if (!passwordRegex.test(password)) {
        e.preventDefault();
        showModal('Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, and one digit.');
        return false;
    }

    if (password !== confirmPassword) {
        e.preventDefault();
        showModal('Passwords do not match. Please confirm your password.');
        return false;
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const fieldsToValidate = ['username', 'email'];
    const nextStep1Button = document.getElementById('next-step1');
    const submitButton = document.querySelector('button[type="submit"]');

    let validationStatus = {
        username: true,
        email: true,
    };

    fieldsToValidate.forEach((field) => {
        const inputField = document.getElementById(field);

        inputField.addEventListener('input', () => {
            validateField(field, inputField.value);
        });
    });

    function validateField(field, value) {
        const errorField = document.getElementById(`${field}-error`);

        // Clear previous error message
        errorField.textContent = '';

        // Perform AJAX request to validate the field
        fetch(`/validate-member-field`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ field, value }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data.valid) {
                    errorField.textContent = data.message;
                    validationStatus[field] = false;
                } else {
                    validationStatus[field] = true;
                }
                toggleButtons();
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    }

    function toggleButtons() {

        const isFormValid = Object.values(validationStatus).every((status) => status === true);

        nextStep1Button.disabled = !isFormValid;
        submitButton.disabled = !isFormValid;
    }

    // Initial state of buttons
    toggleButtons();
});
