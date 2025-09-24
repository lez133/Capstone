function showWarning(message) {
    document.getElementById("warningMessage").innerText = message;
    var warningModal = new bootstrap.Modal(document.getElementById('warningModal'));
    warningModal.show();
}

function validateStep1() {
    validateRequired(['last_name', 'first_name', 'email', 'phone'], 2);
}

function validateStep2() {
    validateRequired(['beneficiary_type', 'birthday', 'gender', 'civil_status'], 3);
}

function validateStep3() {
    const type = document.getElementById('beneficiary_type').value;
    let fields = [];
    if (type === 'Senior Citizen') fields = ['osca_number'];
    if (type === 'PWD') fields = ['pwd_id'];
    if (type === 'Both') fields = ['osca_number', 'pwd_id'];
    if (fields.length > 0) {
        validateRequired(fields, 4);
    } else {
        nextStep(4);
    }
}

function validateStep4() {
    let valid = true;
    const requiredFields = ['password', 'password_confirmation'];

    requiredFields.forEach(id => {
        const input = document.getElementById(id);
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            valid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    const captchaResponse = grecaptcha.getResponse();
    if (!captchaResponse) {
        showWarning("Please complete the reCAPTCHA verification.");
        return;
    }

    if (valid) {
        document.getElementById('registrationForm').submit();
    } else {
        showWarning("Please fill out all required fields before proceeding.");
    }
}

function validateRequired(fieldIds, nextStepNum = null) {
    let valid = true;
    fieldIds.forEach(id => {
        const input = document.getElementById(id);
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            valid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    if (valid) {
        nextStep(nextStepNum);
    } else {
        showWarning("Please fill out all required fields before proceeding.");
    }
}

// Step navigation
function nextStep(step) {
    document.querySelectorAll('.step').forEach(s => s.classList.add('d-none'));
    document.getElementById('step' + step).classList.remove('d-none');
    updateStepper(step);

    if (step === 4) {
        document.getElementById('summary_name').innerText =
            document.getElementById('first_name').value + " " +
            (document.getElementById('middle_name').value || '') + " " +
            document.getElementById('last_name').value + " " +
            (document.getElementById('suffix').value || '');
        document.getElementById('summary_email').innerText = document.getElementById('email').value;
        document.getElementById('summary_phone').innerText = document.getElementById('phone').value;
        document.getElementById('summary_type').innerText = document.getElementById('beneficiary_type').value;
        document.getElementById('summary_birthday').innerText = document.getElementById('birthday').value;
        document.getElementById('summary_age').innerText = document.getElementById('age').value;
        document.getElementById('summary_gender').innerText = document.getElementById('gender').value;
        document.getElementById('summary_civil_status').innerText = document.getElementById('civil_status').value;
        document.getElementById('summary_osca').innerText = document.getElementById('osca_number').value;
        document.getElementById('summary_pwd').innerText = document.getElementById('pwd_id').value;

        const type = document.getElementById('beneficiary_type').value;
        document.querySelectorAll('.senior-only').forEach(el => el.classList.toggle('d-none', type !== 'Senior Citizen' && type !== 'Both'));
        document.querySelectorAll('.pwd-only').forEach(el => el.classList.toggle('d-none', type !== 'PWD' && type !== 'Both'));
    }
}

function prevStep(step) {
    document.querySelectorAll('.step').forEach(s => s.classList.add('d-none'));
    document.getElementById('step' + step).classList.remove('d-none');
    updateStepper(step);
}

function updateStepper(step) {
    let progressPercent = ((step - 1) / 3) * 100;
    document.getElementById('progressLine').style.width = progressPercent + "%";
    for (let i = 1; i <= 4; i++) {
        let el = document.getElementById('stepIndicator' + i);
        if (i < step) {
            el.className = "rounded-circle bg-success text-white d-flex align-items-center justify-content-center mx-auto";
        } else if (i === step) {
            el.className = "rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto";
        } else {
            el.className = "rounded-circle bg-light text-dark border d-flex align-items-center justify-content-center mx-auto";
        }
        el.style.width = "40px";
        el.style.height = "40px";
    }
}

// Dynamic ID fields
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('beneficiary_type').addEventListener('change', function () {
        const type = this.value;
        document.querySelector('.senior-only').classList.toggle('d-none', type !== 'Senior Citizen' && type !== 'Both');
        document.querySelector('.pwd-only').classList.toggle('d-none', type !== 'PWD' && type !== 'Both');
    });

    // Auto-calculate age
    document.getElementById('birthday').addEventListener('change', function () {
        const birthDate = new Date(this.value);
        if (isNaN(birthDate)) {
            document.getElementById('age').value = "";
            return;
        }
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        document.getElementById('age').value = age;
    });
});
