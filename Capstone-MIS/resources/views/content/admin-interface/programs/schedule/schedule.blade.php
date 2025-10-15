@extends('layouts.adminlayout')

@section('title', 'Manage Schedules')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Manage Schedules</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('schedules.create') }}" class="btn btn-primary mb-3 w-100 d-md-inline-block">Create Schedule</a>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Aid Program</th>
                            <th>Barangays</th>
                            <th>Beneficiary Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules as $index => $schedule)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-truncate" style="max-width:120px;">{{ $schedule->aidProgram->aid_program_name }}</td>
                                <td class="text-truncate" style="max-width:120px;">
                                    @php
                                        $barangayNames = !empty($schedule->barangay_ids)
                                            ? \App\Models\Barangay::whereIn('id', $schedule->barangay_ids)->pluck('barangay_name')->toArray()
                                            : [];
                                    @endphp
                                    {{ count($barangayNames) ? implode(', ', $barangayNames) : 'N/A' }}
                                </td>
                                <td>{{ ucfirst($schedule->beneficiary_type) }}</td>
                                <td>{{ $schedule->start_date }}</td>
                                <td>{{ $schedule->end_date }}</td>
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
                                    <div class="d-flex flex-wrap gap-1">
                                        <button type="button"
                                            class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editScheduleModal"
                                            data-id="{{ $schedule->id }}"
                                            data-program="{{ $schedule->aid_program_id }}"
                                            data-beneficiary="{{ $schedule->beneficiary_type }}"
                                            data-start="{{ $schedule->start_date }}"
                                            data-end="{{ $schedule->end_date }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteScheduleModal"
                                            data-id="{{ $schedule->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @if ($schedule->published)
                                            <!-- Unpublish Dropdown -->
                                            <div class="btn-group w-100 w-md-auto">
                                                <button type="button" class="btn btn-sm btn-warning dropdown-toggle w-100 w-md-auto" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ban"></i>
                                                    <span class="d-inline d-md-none ms-1">Unpublish</span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end w-100 w-md-auto">
                                                    <li>
                                                        <form action="{{ route('schedules.unpublish', $schedule->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">Regular Unpublish</button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('schedules.unpublishNotify', $schedule->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">Unpublish & Notify Eligible</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        @else
                                            <!-- Publish Dropdown -->
                                            <div class="btn-group w-100 w-md-auto">
                                                <button type="button" class="btn btn-sm btn-success dropdown-toggle w-100 w-md-auto" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-bullhorn"></i>
                                                    <span class="d-inline d-md-none ms-1">Publish</span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end w-100 w-md-auto">
                                                    <li>
                                                        <form action="{{ route('schedules.publish', $schedule->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">Regular Publish</button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('schedules.publishNotify', $schedule->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">Publish & Notify Eligible</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No schedules found.</td>
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
            <!-- Example fields, adjust as needed -->
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
            <div class="mb-3">
                <label for="editStartDate" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="editStartDate" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="editEndDate" class="form-label">End Date</label>
                <input type="date" name="end_date" id="editEndDate" class="form-control" required>
            </div>
            <!-- Add barangay selection if needed -->
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
  <div class="modal-dialog">
    <form method="POST" id="deleteScheduleForm" class="modal-content">
        @csrf
        @method('DELETE')
        <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="deleteScheduleModalLabel">Delete Schedule</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this schedule?</p>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-danger">Delete</button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var editModal = document.getElementById('editScheduleModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var program = button.getAttribute('data-program');
        var beneficiary = button.getAttribute('data-beneficiary');
        var start = button.getAttribute('data-start');
        var end = button.getAttribute('data-end');
        var form = document.getElementById('editScheduleForm');

        form.action = "{{ url('schedules') }}/" + id;
        document.getElementById('editAidProgram').value = program;
        document.getElementById('editBeneficiaryType').value = beneficiary;
        document.getElementById('editStartDate').value = start;
        document.getElementById('editEndDate').value = end;
    });

    var deleteModal = document.getElementById('deleteScheduleModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var form = document.getElementById('deleteScheduleForm');
        form.action = "{{ url('schedules') }}/" + id;
    });
});
</script>
@endsection
