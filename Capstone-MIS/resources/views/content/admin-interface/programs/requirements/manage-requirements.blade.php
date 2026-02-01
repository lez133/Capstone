@extends('layouts.adminlayout')

@section('title', 'Manage Requirements')

@section('content')
<div class="container mt-4">
    <div class="mb-3">
        <a href="{{ route('programs.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Programs
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Manage Requirements</h5>
        </div>
        <div class="card-body">
            <p>Here you can add new requirements for aid programs and manage existing ones.</p>
        </div>
    </div>

    <!-- Add Requirement Inline -->
    <div class="card mb-4">
        <div class="card-body text-center">
            <form method="POST" action="{{ route('requirements.store') }}" class="d-inline-flex align-items-center gap-2 justify-content-center">
                @csrf
                <input type="text" name="document_requirement" class="form-control" placeholder="Enter new requirement" required style="max-width: 500px; min-width: 300px;">
                <button type="submit" class="btn btn-outline-secondary btn-sm ms-2">
                    <i class="fas fa-plus"></i>Add Requirement/Document
                </button>
            </form>
        </div>
    </div>

    <!-- Requirements List -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Existing Requirements</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Requirement</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requirements as $req)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $req->document_requirement }}</td>
                            <td>
                                <button type="button"
                                    class="btn btn-sm btn-primary ms-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editRequirementModal"
                                    data-id="{{ $req->id }}"
                                    data-name="{{ $req->document_requirement }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button"
                                    class="btn btn-sm btn-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteRequirementModal"
                                    data-id="{{ $req->id }}"
                                    data-name="{{ $req->document_requirement }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">No requirements found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Requirement Modal -->
<div class="modal fade" id="editRequirementModal" tabindex="-1" aria-labelledby="editRequirementModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="editRequirementForm" class="modal-content">
        @csrf
        @method('PUT')
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="editRequirementModalLabel">Edit Requirement</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="text" name="document_requirement" id="editRequirementInput" class="form-control" required>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Requirement
            </button>
        </div>
    </form>
  </div>
</div>

<!-- Delete Requirement Modal -->
<div class="modal fade" id="deleteRequirementModal" tabindex="-1" aria-labelledby="deleteRequirementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <form method="POST" id="deleteRequirementForm" class="modal-content">
        @csrf
        @method('DELETE')
        <div class="modal-header border-0 pb-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center py-4">
            <div class="mb-3">
                <span class="d-inline-block rounded-circle bg-danger bg-opacity-10 p-3">
                    <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
                </span>
            </div>
            <h5 class="fw-bold">Are you sure?</h5>
            <p class="text-muted small mb-3">This action cannot be undone.</p>
            <p id="deleteRequirementName" class="fw-semibold mb-3"></p>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-danger btn-lg">Delete Requirement</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var editModal = document.getElementById('editRequirementModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var reqId = button.getAttribute('data-id');
        var reqName = button.getAttribute('data-name');
        var input = document.getElementById('editRequirementInput');
        var form = document.getElementById('editRequirementForm');
        input.value = reqName;
        form.action = "{{ url('programs/requirements') }}/" + reqId;
    });

    var deleteModal = document.getElementById('deleteRequirementModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var reqId = button.getAttribute('data-id');
        var reqName = button.getAttribute('data-name');
        var form = document.getElementById('deleteRequirementForm');
        var reqNameLabel = document.getElementById('deleteRequirementName');
        form.action = "{{ url('programs/requirements') }}/" + reqId;
        reqNameLabel.textContent = reqName;
    });
});
</script>
@endsection
