@extends('layouts.adminlayout')

@section('title', 'Manage Schedules')

@section('content')
<style>
.card-header .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
</style>
<div class="container mt-5">

    @if(session('sms_summary'))
        <div id="smsSummaryAlert" class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>SMS:</strong> {{ session('sms_summary') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const el = document.getElementById('smsSummaryAlert');
                if (!el) return;
                // fade after 3s then remove (uses Bootstrap alert close if available)
                setTimeout(() => {
                    try {
                        const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
                        bsAlert.close();
                    } catch (e) {
                        el.style.transition = 'opacity 0.5s ease';
                        el.style.opacity = '0';
                        setTimeout(() => el.remove(), 600);
                    }
                }, 3000);
            });
        </script>
    @endif

    <div class="card border-0 shadow-sm">
        <!-- Header -->
        <div class="card-header text-white shadow-sm d-flex flex-column flex-md-row align-items-md-center justify-content-between py-3"
        style="background: linear-gradient(135deg, #4e73df, #1cc88a); border-bottom: 3px solid rgba(255,255,255,0.2); border-radius: 0.5rem;">
        <h4 class="fw-bold mb-2 mb-md-0">Manage Schedules</h4>
        <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center">
            <a href="{{ route('schedules.create') }}" class="btn btn-light btn-sm d-flex align-items-center gap-1 rounded shadow-sm" style="transition: transform 0.2s, box-shadow 0.2s;">
                <i class="fas fa-plus text-primary"></i> Create Schedule
            </a>
            <a href="{{ route('schedules.calendar') }}" class="btn btn-info btn-sm text-white d-flex align-items-center gap-1 rounded shadow-sm" style="transition: transform 0.2s, box-shadow 0.2s;">
                <i class="fas fa-calendar-alt"></i> View Calendar
            </a>
            <form method="GET" action="{{ route('schedules.index') }}">
                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="recent" {{ $sort === 'recent' ? 'selected' : '' }}>Recently Created</option>
                    <option value="date_asc" {{ $sort === 'date_asc' ? 'selected' : '' }}>Earliest Schedule</option>
                    <option value="date_desc" {{ $sort === 'date_desc' ? 'selected' : '' }}>Latest Schedule</option>
                </select>
            </form>
        </div>
    </div>

        <!-- Table -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Aid Program</th>
                            <th>Barangays</th>
                            <th>Beneficiary Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules as $index => $schedule)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="text-truncate" style="max-width:150px;">
                                <span class="fw-semibold">{{ $schedule->aidProgram->aid_program_name }}</span>
                            </td>
                            <td class="text-truncate" style="max-width:150px;">
                                @php
                                    $barangayNames = !empty($schedule->barangay_ids)
                                        ? \App\Models\Barangay::whereIn('id', $schedule->barangay_ids)->pluck('barangay_name')->toArray()
                                        : [];
                                @endphp
                                <span class="badge bg-light text-dark">{{ count($barangayNames) ? implode(', ', $barangayNames) : 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($schedule->beneficiary_type) }}</span>
                            </td>
                            <td>
                                <span class="text-nowrap">{{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') }}</span>
                            </td>
                            <td>
                                <span class="text-nowrap">{{ \Carbon\Carbon::parse($schedule->end_date)->format('M d, Y') }}</span>
                            </td>
                            <td>
                                @if($schedule->status === 'Upcoming')
                                    <span class="badge bg-info text-dark">Upcoming</span>
                                @elseif($schedule->status === 'Ongoing')
                                    <span class="badge bg-success">Ongoing</span>
                                @else
                                    <span class="badge bg-secondary">Completed</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                    <!-- Edit Button -->
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#editScheduleModal"
                                        data-id="{{ $schedule->id }}"
                                        data-program="{{ $schedule->aid_program_id }}"
                                        data-beneficiary="{{ $schedule->beneficiary_type }}"
                                        data-start="{{ $schedule->start_date }}"
                                        data-end="{{ $schedule->end_date }}"
                                        data-barangays='@json($schedule->barangay_ids)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <!-- Delete Button -->
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal" data-bs-target="#deleteScheduleModal"
                                        data-id="{{ $schedule->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <!-- Publish/Unpublish Dropdown -->
                                    <div class="btn-group btn-group-sm">
                                        @if ($schedule->published)
                                            <button type="button" class="btn btn-outline-warning dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <form action="{{ route('schedules.unpublish', $schedule->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">Unpublish</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('schedules.unpublishNotify', $schedule->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">Unpublish & Notify</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        @else
                                            <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-bullhorn"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <form action="{{ route('schedules.publish', $schedule->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">Publish</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('schedules.publishNotify', $schedule->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">Publish & Notify</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">No schedules found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="editScheduleForm" class="modal-content">
        @csrf
        @method('PUT')
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="editScheduleModalLabel">Edit Schedule</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="editAidProgram" class="form-label">Aid Program</label>
                <select name="aid_program_id" id="editAidProgram" class="form-select" required>
                    @foreach($aidPrograms as $program)
                        <option value="{{ $program->id }}">{{ $program->aid_program_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="editBeneficiaryType" class="form-label">Beneficiary Type</label>
                <select name="beneficiary_type" id="editBeneficiaryType" class="form-select" required>
                    <option value="senior">Senior Citizens</option>
                    <option value="pwd">Persons with Disabilities</option>
                    <option value="both">Both</option>
                </select>
            </div>
            <div class="mb-3" id="edit-barangay-section">
                <label for="editBarangays" class="form-label">Select Barangays</label>
                <select name="barangay_ids[]" id="editBarangays" class="form-select" multiple>
                    @foreach(\App\Models\Barangay::orderBy('barangay_name')->get() as $barangay)
                        <option value="{{ $barangay->id }}">{{ $barangay->barangay_name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Search, select multiple barangays, or select all.</small>
            </div>
            <div class="mb-3">
                <label for="editStartDate" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="editStartDate" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="editEndDate" class="form-label">End Date</label>
                <input type="date" name="end_date" id="editEndDate" class="form-control" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Update Schedule</button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
    </form>
  </div>
</div>

<!-- Delete Schedule Modal -->
<div class="modal fade" id="deleteScheduleModal" tabindex="-1" aria-labelledby="deleteScheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" id="deleteScheduleForm" class="modal-content">
      @csrf
      @method('DELETE')
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <div class="mx-auto d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10" style="width:64px; height:64px;">
            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:1.6rem;"></i>
          </div>
        </div>

        <h5 class="modal-title fw-bold mb-2" id="deleteScheduleModalLabel">Are you sure?</h5>
        <p class="text-muted mb-4">Are you sure you want to delete this schedule? This action cannot be undone.</p>

        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-danger">Delete Schedule</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Choices.js CSS/JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Choices.js for barangay multi-select in edit modal
    let editChoices;
    const barangaySelect = document.getElementById('editBarangays');
    if (barangaySelect) {
        editChoices = new Choices(barangaySelect, {
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
            editChoices.setChoices(
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
            editChoices.removeActiveItems();
        });
    }

    // Edit modal
    var editModal = document.getElementById('editScheduleModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.dataset.id;
        var program = button.dataset.program;
        var beneficiary = button.dataset.beneficiary;
        var start = button.dataset.start;
        var end = button.dataset.end;
        var barangays = button.dataset.barangays ? JSON.parse(button.dataset.barangays) : [];

        var form = document.getElementById('editScheduleForm');
        form.action = "{{ url('schedules') }}/" + id;
        document.getElementById('editAidProgram').value = program;
        document.getElementById('editBeneficiaryType').value = beneficiary;
        document.getElementById('editStartDate').value = start;
        document.getElementById('editEndDate').value = end;

        // Set barangays for Choices.js
        if (editChoices) {
            editChoices.removeActiveItems();
            barangays.forEach(function (barangayId) {
                editChoices.setChoiceByValue(String(barangayId));
            });
        }
    });

    // Delete modal
    var deleteModal = document.getElementById('deleteScheduleModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.dataset.id;
        document.getElementById('deleteScheduleForm').action = "{{ url('schedules') }}/" + id;
    });
});
</script>


@endsection
