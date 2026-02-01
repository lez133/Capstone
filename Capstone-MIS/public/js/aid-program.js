document.addEventListener('DOMContentLoaded', function() {
    // Toggle List/Card View
    const toggleBtn = document.getElementById('toggleViewBtn');
    const cardView = document.getElementById('cardView');
    const listView = document.getElementById('listView');
    const searchInput = document.getElementById('searchAidProgram');

    if (toggleBtn && cardView && listView) {
        toggleBtn.addEventListener('click', function() {
            if (cardView.style.display === 'none') {
                cardView.style.display = '';
                listView.style.display = 'none';
                toggleBtn.innerHTML = '<i class="fa fa-list"></i> Toggle List/Card View';
            } else {
                cardView.style.display = 'none';
                listView.style.display = '';
                toggleBtn.innerHTML = '<i class="fa fa-th"></i> Toggle List/Card View';
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            document.querySelectorAll('.aid-program-item').forEach(function(item) {
                const nameEl = item.querySelector('.aid-program-name');
                const name = nameEl ? nameEl.textContent.toLowerCase() : '';
                item.style.display = name.includes(term) ? '' : 'none';
            });
        });
    }

    // Robust response handler: parse JSON when possible, fallback to text
    function parseResponse(res) {
        if (res.status === 419) {
            throw { message: 'Session expired. Reload page and try again.' };
        }
        const contentType = res.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            return res.json().then(data => {
                if (!res.ok) throw data;
                return data;
            });
        }
        // fallback: try json, else text
        return res.text().then(text => {
            try {
                const parsed = JSON.parse(text);
                if (!res.ok) throw parsed;
                return parsed;
            } catch (e) {
                // HTML or plain text returned (error page) -> surface message
                throw { message: 'Invalid server response (HTML). Check server logs.' };
            }
        });
    }

    // Add Requirement (unchanged behavior, uses blade-provided window.requirementStoreRoute)
    const addRequirementBtn = document.getElementById('addRequirementBtn');
    if (addRequirementBtn) {
        addRequirementBtn.onclick = function() {
            const newReqInput = document.getElementById('newRequirement');
            const newReq = newReqInput.value.trim();
            if (!newReq) return;
            fetch(window.requirementStoreRoute, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": window.csrfToken
                },
                body: JSON.stringify({ document_requirement: newReq })
            })
            .then(parseResponse)
            .then(data => {
                if (data.id) {
                    if (!document.getElementById('req' + data.id)) {
                        const div = document.createElement('div');
                        div.className = 'form-check';
                        div.innerHTML = `
                            <input class="form-check-input" type="checkbox" name="requirements[]" value="${data.id}" id="req${data.id}" checked>
                            <label class="form-check-label" for="req${data.id}">${data.document_requirement}</label>
                        `;
                        document.getElementById('requirement-checkboxes').appendChild(div);
                    }
                    newReqInput.value = '';
                    showRequirementSuccess('Requirement added successfully!');
                } else {
                    showRequirementError(data.message || 'Could not add requirement.');
                }
            })
            .catch(err => {
                if (err && err.errors) showRequirementError(Object.values(err.errors).join('<br>'));
                else showRequirementError(err.message || 'Server error. Please try again.');
            });
        };
    }

    // Add Program Type (uses blade-provided window.programTypeStoreRoute)
    const addProgramTypeBtn = document.getElementById('addProgramTypeBtn');
    if (addProgramTypeBtn) {
        addProgramTypeBtn.onclick = function() {
            const newTypeInput = document.getElementById('newProgramType');
            const newType = newTypeInput.value.trim();
            const errorDiv = document.getElementById('programTypeError');
            const successDiv = document.getElementById('programTypeSuccess');
            if (errorDiv) { errorDiv.classList.add('d-none'); errorDiv.textContent = ''; }
            if (successDiv) { successDiv.classList.add('d-none'); successDiv.textContent = ''; }

            if (!newType) {
                if (errorDiv) { errorDiv.textContent = 'Program type name is required.'; errorDiv.classList.remove('d-none'); }
                return;
            }

            fetch(window.programTypeStoreRoute, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": window.csrfToken
                },
                body: JSON.stringify({ program_type_name: newType })
            })
            .then(parseResponse)
            .then(data => {
                if (data.id && data.program_type_name) {
                    const select = document.getElementById('program_type_id');
                    const option = document.createElement('option');
                    option.value = data.id;
                    option.textContent = data.program_type_name;
                    if (select) select.appendChild(option);
                    if (select) select.value = data.id;
                    if (successDiv) {
                        successDiv.textContent = 'Program type added!';
                        successDiv.classList.remove('d-none');
                        setTimeout(() => { successDiv.classList.add('d-none'); }, 2000);
                    }
                    newTypeInput.value = '';
                } else {
                    if (errorDiv) { errorDiv.textContent = data.message || 'Could not add program type.'; errorDiv.classList.remove('d-none'); }
                }
            })
            .catch(err => {
                if (err && err.errors) {
                    if (errorDiv) { errorDiv.textContent = Object.values(err.errors).join(' '); }
                } else {
                    if (errorDiv) { errorDiv.textContent = err.message || 'Server error. Please try again.'; }
                }
                if (errorDiv) errorDiv.classList.remove('d-none');
            });
        };
    }

    // UI helpers (success/error)
    window.showRequirementSuccess = function(message) {
        let alertDiv = document.getElementById('requirement-success-alert');
        if (!alertDiv) {
            alertDiv = document.createElement('div');
            alertDiv.id = 'requirement-success-alert';
            alertDiv.className = 'alert alert-success mt-2';
            const parent = document.getElementById('requirement-checkboxes').parentNode;
            parent.insertBefore(alertDiv, document.getElementById('requirement-checkboxes'));
        }
        alertDiv.textContent = message;
        setTimeout(() => { alertDiv.remove(); }, 2000);
    };

    window.showRequirementError = function(message) {
        const body = document.getElementById('requirementErrorModalBody');
        if (body) body.innerHTML = message;
        const modalEl = document.getElementById('requirementErrorModal');
        if (modalEl) {
            var errorModal = new bootstrap.Modal(modalEl);
            errorModal.show();
        } else {
            alert(message);
        }
    };

    // Schedule toggle
    const toggle = document.getElementById('createScheduleNow');
    const scheduleFields = document.getElementById('scheduleFields');
    const beneficiaryType = document.getElementById('beneficiary_type');
    const barangaySelect = document.getElementById('barangay_ids');

    if (toggle && scheduleFields) {
        toggle.addEventListener('change', () => {
            scheduleFields.style.display = toggle.checked ? '' : 'none';
            // initialize searchable multi-select when schedule is enabled
            if (toggle.checked) {
                initModalChoices();
            }
        });
        if (toggle.checked) scheduleFields.style.display = '';
    }
    // ensure if scheduleFields are shown on modal open we initialize choices (existing code already calls initModalChoices on shown.bs.modal)
    if (beneficiaryType && barangaySelect) {
        beneficiaryType.addEventListener('change', () => {
            const val = beneficiaryType.value;
            barangaySelect.closest('.mb-3').style.display = (val === 'senior' || val === 'both') ? '' : 'none';
        });
    }

    // Choices.js for barangay multi-select
    const modalBarangaySelect = document.getElementById('barangay_ids');
    let modalChoices;
    function initModalChoices() {
        if (!modalBarangaySelect) return;
        if (modalChoices) return;
        modalChoices = new Choices(modalBarangaySelect, {
            removeItemButton: true,
            searchEnabled: true,
            placeholderValue: 'Search barangays...',
            searchPlaceholderValue: 'Type to search...',
            itemSelectText: '',
            shouldSort: false,
        });
        // Select All / Clear All buttons
        const wrapper = modalBarangaySelect.parentNode;
        const btnContainer = document.createElement('div');
        btnContainer.className = 'mt-2';
        const selectAllBtn = document.createElement('button');
        selectAllBtn.type = 'button';
        selectAllBtn.className = 'btn btn-sm btn-outline-primary me-2';
        selectAllBtn.textContent = 'Select All';
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'btn btn-sm btn-outline-danger';
        clearBtn.textContent = 'Clear All';
        btnContainer.appendChild(selectAllBtn);
        btnContainer.appendChild(clearBtn);
        wrapper.appendChild(btnContainer);
        selectAllBtn.addEventListener('click', () => {
            Array.from(modalBarangaySelect.options).forEach(opt => opt.selected = true);
            modalChoices.setChoices(
                Array.from(modalBarangaySelect.options).map(option => ({
                    value: option.value,
                    label: option.text,
                    selected: true,
                })),
                'value',
                'label',
                true
            );
        });
        clearBtn.addEventListener('click', () => {
            modalChoices.removeActiveItems();
        });
    }

    // Modal events
    const addModal = document.getElementById('addAidProgramModal');
    if (addModal) {
        addModal.addEventListener('shown.bs.modal', () => {
            if (toggle && toggle.checked) {
                scheduleFields.style.display = '';
                initModalChoices();
            }
        });
        addModal.addEventListener('hidden.bs.modal', () => {
            // Optionally destroy choices instance
            // if (modalChoices) { modalChoices.destroy(); modalChoices = null; }
        });
    }

    // Highlight and select default background image
    document.querySelectorAll('.default-bg-thumb').forEach(function(img) {
        img.addEventListener('click', function() {
            document.getElementById('default_background').value = this.getAttribute('data-value');
            document.querySelectorAll('.default-bg-thumb').forEach(i => i.classList.remove('border-primary'));
            this.classList.add('border-primary');
        });
    });
});
