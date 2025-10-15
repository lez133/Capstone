@extends('layouts.adminlayout')

@section('title', 'Create Schedule')

@section('content')

<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Create Schedule</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('schedules.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="aid_program_id" class="form-label">Aid Program</label>
                    <select name="aid_program_id" id="aid_program_id" class="form-select" required>
                        <option value="">Select Aid Program</option>
                        @foreach ($aidPrograms as $program)
                            <option value="{{ $program->id }}">{{ $program->aid_program_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="beneficiary_type" class="form-label">Beneficiary Type</label>
                    <select name="beneficiary_type" id="beneficiary_type" class="form-select" required>
                        <option value="">Select Beneficiary Type</option>
                        <option value="senior">Senior Citizens</option>
                        <option value="pwd">Persons with Disabilities</option>
                        <option value="both">Both</option>
                    </select>
                </div>

                <div class="mb-3" id="barangay-section" style="display: none;">
                    <label for="barangay_ids" class="form-label">Select Barangays</label>
                    <select name="barangay_ids[]" id="barangay_ids" class="form-select" multiple>
                        @foreach ($barangays as $barangay)
                            <option value="{{ $barangay->id }}">{{ $barangay->barangay_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Search, select multiple barangays, or select all.</small>
                </div>

                <div class="mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="datetime-local" name="start_date" id="start_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="datetime-local" name="end_date" id="end_date" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Create Schedule</button>
            </form>
        </div>
    </div>
</div>

<!-- Choices.js -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const beneficiaryType = document.getElementById('beneficiary_type');
        const barangaySection = document.getElementById('barangay-section');
        const barangaySelect = document.getElementById('barangay_ids');
        let choices;

        beneficiaryType.addEventListener('change', function () {
            if (this.value === 'senior' || this.value === 'both') {
                barangaySection.style.display = '';
                if (!choices) {
                    choices = new Choices(barangaySelect, {
                        removeItemButton: true,
                        searchEnabled: true,
                        placeholderValue: 'Search barangays...',
                        searchPlaceholderValue: 'Type to search...',
                        itemSelectText: '',
                        shouldSort: false,
                    });

                    // Add "Select all" and "Clear all" buttons
                    const selectAllBtn = document.createElement('button');
                    selectAllBtn.type = 'button';
                    selectAllBtn.innerText = 'Select All';
                    selectAllBtn.classList.add('btn', 'btn-sm', 'btn-outline-primary', 'mt-2', 'me-2');

                    const clearBtn = document.createElement('button');
                    clearBtn.type = 'button';
                    clearBtn.innerText = 'Clear All';
                    clearBtn.classList.add('btn', 'btn-sm', 'btn-outline-danger', 'mt-2');

                    barangaySelect.parentNode.appendChild(selectAllBtn);
                    barangaySelect.parentNode.appendChild(clearBtn);

                    selectAllBtn.addEventListener('click', () => {
                        barangaySelect.querySelectorAll('option').forEach(option => {
                            option.selected = true;
                        });
                        choices.setChoices(
                            Array.from(barangaySelect.options).map(option => ({
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
                        choices.removeActiveItems();
                    });
                }
            } else {
                barangaySection.style.display = 'none';
                if (choices) {
                    choices.destroy();
                    choices = null;
                }
            }
        });
    });
</script>

<!-- Error Modal -->
<div class="modal fade" id="scheduleErrorModal" tabindex="-1" aria-labelledby="scheduleErrorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="scheduleErrorModalLabel">Schedule Creation Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="scheduleErrorModalBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let errorMessages = `{!! implode('<br>', $errors->all()) !!}`;
            document.getElementById('scheduleErrorModalBody').innerHTML = errorMessages;
            let errorModal = new bootstrap.Modal(document.getElementById('scheduleErrorModal'));
            errorModal.show();
        });
    </script>
@endif
@endsection
