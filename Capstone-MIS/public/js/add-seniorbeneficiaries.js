document.getElementById('birthday')?.addEventListener('change', function () {
    const birthday = new Date(this.value);
    const today = new Date();
    let age = today.getFullYear() - birthday.getFullYear();
    const monthDiff = today.getMonth() - birthday.getMonth();
    const dayDiff = today.getDate() - birthday.getDate();

    if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
        age--;
    }

    document.getElementById('age').value = age >= 0 ? age : '';
});

// Add Beneficiary Form Submission
document.getElementById('addBeneficiaryForm')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

    fetch(this.getAttribute('action'), {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showToast('Success', data.message, 'success');

            // Reset form
            document.getElementById('addBeneficiaryForm').reset();
            document.getElementById('age').value = '';

            // Clear old errors if any
            const errorAlert = document.querySelector('.alert-danger');
            if (errorAlert) {
                errorAlert.remove();
            }

            // DO NOT REDIRECT - Stay on page
        } else {
            showToast('Error', data.message || 'An error occurred', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'An error occurred while adding the beneficiary', 'danger');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});

// Toast/Alert Helper Function
function showToast(title, message, type = 'success') {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show shadow-sm rounded-3" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <strong>${title}:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', alertHtml);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Import functionality via AJAX
document.getElementById('importForm')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressMessage = document.getElementById('progressMessage');

    progressContainer.style.display = 'block';
    progressBar.style.width = '0%';
    progressMessage.textContent = 'Uploading and processing data...';

    fetch(this.getAttribute('action') || importRoute, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        progressBar.style.width = '100%';
        if (data.success && data.insertedRows > 0) {
            progressMessage.innerHTML = `
                <strong style="color: green;">✓ Import Successful!</strong><br>
                Total rows: <strong>${data.totalRows}</strong><br>
                Inserted rows: <strong>${data.insertedRows}</strong>
            `;
            showToast('Success', `Successfully imported ${data.insertedRows} beneficiary(ies)`, 'success');

            if (Array.isArray(data.insertedIds) && data.insertedIds.length > 0) {
                showUndoButton(data.insertedIds);
            }

            // Reset import form
            document.getElementById('importForm').reset();

            // Hide progress after 3 seconds
            setTimeout(() => {
                progressContainer.style.display = 'none';
                progressBar.style.width = '0%';
            }, 3000);
        } else {
            // Only show error as toast, do not display at the bottom/progress area
            progressContainer.style.display = 'none';
            showToast('Error', data.error || 'No beneficiaries were imported. Please check your CSV format and data.', 'danger');
        }
    })
    .catch(error => {
        progressBar.style.width = '100%';
        progressMessage.textContent = 'An error occurred during the import process.';
        console.error(error);
        showToast('Error', 'An error occurred during import', 'danger');
    });
});

let lastImportIds = [];
const undoBtn = document.getElementById('undoImportBtn');

function showUndoButton(ids) {
    lastImportIds = ids;
    if (undoBtn) undoBtn.style.display = 'inline-block';
}

// Undo Import Modal logic
if (undoBtn) {
    undoBtn.addEventListener('click', function () {
        if (!Array.isArray(lastImportIds) || lastImportIds.length === 0) return;

        if (!confirm('Are you sure you want to undo the last import? This will delete the imported records.')) return;

        undoBtn.disabled = true;
        const originalText = undoBtn.innerHTML;
        undoBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Undoing...';

        fetch('/beneficiaries/senior/undo-import', {
            method: 'POST',
            body: JSON.stringify({
                encrypted_barangay_id: document.querySelector('input[name="encrypted_barangay_id"]').value
            }),
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                showToast('Success', 'Import undone successfully.', 'success');
                lastImportIds = [];
                undoBtn.style.display = 'none';
            } else {
                showToast('Error', resp.error || 'Failed to undo import.', 'danger');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Error', 'Failed to undo import.', 'danger');
        })
        .finally(() => {
            undoBtn.disabled = false;
            undoBtn.innerHTML = originalText;
        });
    });
}
