document.addEventListener('DOMContentLoaded', () => {

    // Manual validity preview
    const validityInput = document.getElementById('validity_years_input');
    const validToPreviewInput = document.getElementById('valid_to_preview_input');
    function updatePreviewInput() {
        const years = parseInt(validityInput.value) || 0;
        const now = new Date();
        const expiry = new Date(now);
        expiry.setFullYear(now.getFullYear() + years);
        validToPreviewInput.value = expiry.toISOString().split('T')[0];
    }
    if (validityInput && validToPreviewInput) {
        validityInput.addEventListener('input', updatePreviewInput);
        updatePreviewInput();
    }

    // CSV validity preview
    const validityImport = document.getElementById('validity_years_import');
    const validToPreviewImport = document.getElementById('valid_to_preview_import');
    function updatePreviewImport() {
        const years = parseInt(validityImport.value) || 0;
        const now = new Date();
        const expiry = new Date(now);
        expiry.setFullYear(now.getFullYear() + years);
        validToPreviewImport.value = expiry.toISOString().split('T')[0];
    }
    if (validityImport && validToPreviewImport) {
        validityImport.addEventListener('input', updatePreviewImport);
        updatePreviewImport();
    }

    // Register selected disability to input field
    const disabilitySelect = document.getElementById('disabilitySelect');
    const disabilityInput = document.getElementById('type_of_disability');
    if (disabilitySelect && disabilityInput) {
        disabilitySelect.addEventListener('change', function () {
            if (this.value) disabilityInput.value = this.value;
        });
    }

    // Register selected remarks to input field
    const remarksSelect = document.getElementById('remarksSelect');
    const remarksInput = document.getElementById('remarks');
    if (remarksSelect && remarksInput) {
        remarksSelect.addEventListener('change', function () {
            if (this.value) remarksInput.value = this.value;
        });
    }

    // Ensure a showToast helper exists (used for undo success). If not, define a minimal one.
    if (typeof window.showToast !== 'function') {
        window.showToast = function (title, message, type = 'success') {
            // Try Bootstrap toast if available
            try {
                const toastEl = document.createElement('div');
                toastEl.className = `toast align-items-center text-bg-${type} border-0`;
                toastEl.setAttribute('role', 'alert');
                toastEl.setAttribute('aria-live', 'assertive');
                toastEl.setAttribute('aria-atomic', 'true');
                toastEl.style.position = 'fixed';
                toastEl.style.top = '1rem';
                toastEl.style.right = '1rem';
                toastEl.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body"><strong>${title}:</strong> ${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>`;
                document.body.appendChild(toastEl);
                const bsToast = bootstrap?.Toast ? new bootstrap.Toast(toastEl, { delay: 5000 }) : null;
                if (bsToast) bsToast.show();
                else setTimeout(() => toastEl.remove(), 5000);
            } catch (e) {
                // Fallback
                alert(`${title}: ${message}`);
            }
        };
    }

    const undoBtn = document.getElementById('undoImportBtn');
    let lastImportIds = [];

    const importForm = document.getElementById('importForm');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressMessage = document.getElementById('progressMessage');

    if (importForm) {
        importForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            progressContainer.style.display = 'block';
            progressBar.style.width = '25%';
            progressMessage.textContent = 'Uploading file...';

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
                progressBar.style.width = '100%';
                if (data.success) {
                    progressMessage.textContent = 'Import successful.';

                    // If there were only skipped (exact-duplicate) rows, surface a WARNING
                    const inserted = Number(data.insertedRows || 0);
                    const skipped = Number(data.skippedRows || 0);
                    const msg = data.message || (inserted ? `Successfully imported ${inserted} beneficiary(ies).` : (skipped ? `${skipped} duplicate row(s) were skipped.` : 'Import completed.'));
                    const toastType = inserted > 0 ? 'success' : (skipped > 0 ? 'warning' : 'success');
                    showToast(toastType === 'warning' ? 'Warning' : 'Success', msg, toastType);

                    // Accept multiple possible key names from backend
                    const returnedIds = Array.isArray(data.insertedIds) ? data.insertedIds
                        : Array.isArray(data.importedIds) ? data.importedIds
                        : Array.isArray(data.imported_ids) ? data.imported_ids
                        : [];

                    if (returnedIds.length > 0) {
                        lastImportIds = returnedIds;
                        if (undoBtn) undoBtn.style.display = 'inline-block';
                    } else {
                        lastImportIds = [];
                        if (undoBtn) undoBtn.style.display = 'none';
                    }

                    importForm.reset();

                    // hide only the progress UI, do NOT hide the undo button (user requested it remain visible)
                    setTimeout(() => {
                        progressContainer.style.display = 'none';
                        progressBar.style.width = '0%';
                        progressMessage.textContent = '';
                    }, 2500);
                } else {
                    progressMessage.textContent = data.error || 'Invalid file for import. Please check the file and try again.';
                    showToast('Error', data.error || 'Invalid file for import. Please check the file and try again.', 'danger');
                    lastImportIds = [];
                    if (undoBtn) undoBtn.style.display = 'none';
                }
            })
            .catch(error => {
                progressBar.style.width = '100%';
                progressMessage.textContent = 'Invalid file for import. Please check the file and try again.';
                console.error(error);
                showToast('Error', 'Invalid file for import. Please check the file and try again.', 'danger');
                lastImportIds = [];
                if (undoBtn) undoBtn.style.display = 'none';
            });
        });
    }

    // Undo click handler
    if (undoBtn) {
        const undoModalEl = document.getElementById('undoImportModal');
        const undoModalBody = document.getElementById('undoImportModalBody');
        const confirmUndoBtn = document.getElementById('confirmUndoBtn');
        let bsUndoModal = null;

        // initialize bootstrap modal instance when available
        if (undoModalEl && typeof bootstrap !== 'undefined') {
            bsUndoModal = new bootstrap.Modal(undoModalEl, { keyboard: true });
        }

        // open modal on undo button click
        undoBtn.addEventListener('click', function () {
            if (!Array.isArray(lastImportIds) || lastImportIds.length === 0) return;
            if (undoModalBody) {
                undoModalBody.textContent = `This will delete ${lastImportIds.length} imported record(s). Are you sure you want to proceed?`;
            }
            if (bsUndoModal) bsUndoModal.show();
        });

        // confirm undo (single handler)
        if (confirmUndoBtn) {
            confirmUndoBtn.addEventListener('click', function () {
                if (!Array.isArray(lastImportIds) || lastImportIds.length === 0) return;

                confirmUndoBtn.disabled = true;
                const originalLabel = confirmUndoBtn.innerHTML;
                confirmUndoBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Undoing...';

                fetch('/beneficiaries/pwd/undo-import', {
                    method: 'POST',
                    body: JSON.stringify({ ids: lastImportIds }),
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
                        if (undoBtn) undoBtn.style.display = 'none';
                        if (bsUndoModal) bsUndoModal.hide();
                    } else {
                        showToast('Error', resp.error || 'Failed to undo import.', 'danger');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Error', 'Failed to undo import.', 'danger');
                })
                .finally(() => {
                    confirmUndoBtn.disabled = false;
                    confirmUndoBtn.innerHTML = originalLabel;
                });
            });
        }
    }

    // Auto-calculate age when birthday changes
    const birthdayInput = document.getElementById('birthday');
    const ageInput = document.getElementById('age');
    if (birthdayInput && ageInput) {
        birthdayInput.addEventListener('change', function () {
            const birthday = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthday.getFullYear();
            const monthDiff = today.getMonth() - birthday.getMonth();
            const dayDiff = today.getDate() - birthday.getDate();

            if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
                age--;
            }

            ageInput.value = age >= 0 ? age : '';
        });
    }

    // Toast/Alert Helper Function
    function showToast(title, message, type = 'success') {
        let alertClass = 'alert-danger';
        if (type === 'success') alertClass = 'alert-success';
        else if (type === 'warning') alertClass = 'alert-warning';
        else if (type === 'info') alertClass = 'alert-info';
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
});
