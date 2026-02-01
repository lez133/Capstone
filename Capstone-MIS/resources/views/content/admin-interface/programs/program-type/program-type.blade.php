@extends('layouts.adminlayout')

@section('title', 'Program Types')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
    .card-action { transition: box-shadow 0.3s, transform 0.3s; }
    .card-action:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.15); transform: scale(1.03); }
    .fade-in { animation: fadeInUp 0.6s; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .table-responsive { overflow-x: auto; }
</style>
<div class="container py-4">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('programs.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Programs
        </a>
    </div>

    <div class="row g-3 mb-4">
        <!-- Add Program Type Card -->
        <div class="col-md-6 col-12">
            <div class="card card-action fade-in">
                <div class="card-body">
                    <h5><i class="fa-solid fa-plus text-primary"></i> Add Program Type</h5>
                    <form method="POST" action="{{ route('program-types.store') }}">
                        @csrf
                        <div class="input-group mb-2">
                            <input type="text" name="program_type_name" class="form-control" placeholder="Program Type Name" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-2"><i class="fa fa-save"></i> Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Program Type List -->
    <div class="card fade-in">
        <div class="card-header bg-primary text-white">
            <i class="fa fa-list"></i> Program Type List
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><i class="fa fa-tag"></i> Program Type</th>
                        <th><i class="fa fa-calendar"></i> Added</th>
                        <th class="text-end"><i class="fa fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programTypes as $i => $programType)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>
                                @if(request('edit') == $programType->id)
                                    <form method="POST" action="{{ route('program-types.update', $programType->id) }}" class="d-flex align-items-center">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="program_type_name" class="form-control form-control-sm me-2" value="{{ $programType->program_type_name }}" required>
                                        <button type="submit" class="btn btn-success btn-sm me-1"><i class="fa fa-check"></i></button>
                                        <a href="{{ route('program-types.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-times"></i></a>
                                    </form>
                                @else
                                    {{ $programType->program_type_name }}
                                @endif
                            </td>
                            <td>{{ $programType->created_at->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <a href="{{ route('program-types.index', ['edit' => $programType->id]) }}" class="btn btn-sm btn-warning me-1" style="background: none; border: none;">
                                    <i class="fa fa-edit" style="color: #ffc107;"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger btn-delete-program-type" data-id="{{ $programType->id }}" style="background: none; border: none;">
                                    <i class="fa fa-trash" style="color: #dc3545;"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No program types found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteProgramTypeModal" tabindex="-1" aria-labelledby="deleteProgramTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0" style="border-radius: 18px;">
      <div class="modal-header border-0" style="background: none;">
        <div class="mx-auto text-center">
          <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:56px;height:56px;">
            <i class="fa fa-exclamation-triangle text-danger fs-2"></i>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center pt-0">
        <h4 class="fw-bold mb-2">Are you sure?</h4>
        <p class="text-muted mb-4">Are you sure you want to delete this program type?<br>This action cannot be undone.</p>
        <button type="button" class="btn btn-danger w-100 mb-2 rounded-pill" id="confirmDeleteBtn">
          <i class="fa fa-trash me-1"></i>Delete Program Type
        </button>
        <button type="button" class="btn btn-outline-secondary w-100 rounded-pill" data-bs-dismiss="modal">
          Cancel
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="deleteSuccessModal" tabindex="-1" aria-labelledby="deleteSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 18px;">
      <div class="modal-header bg-success text-white border-0 rounded-top-4">
        <h5 class="modal-title" id="deleteSuccessModalLabel">
          <i class="fa fa-check-circle me-2"></i> Program Type Deleted
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="fs-5 mb-0">Program type was successfully deleted.</p>
      </div>
      <div class="modal-footer bg-light justify-content-center border-0 rounded-bottom-4">
        <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
window.requirementStoreRoute = "{{ route('requirements.store') }}";
window.programTypeStoreRoute = "{{ route('program-types.store') }}";
window.csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{ asset('js/aid-program.js') }}"></script>

@push('scripts')
<script>
let programTypeIdToDelete = null;

// Attach modal trigger to delete buttons
document.querySelectorAll('.btn-delete-program-type').forEach(function(btn) {
    btn.addEventListener('click', function() {
        programTypeIdToDelete = this.getAttribute('data-id');
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteProgramTypeModal'));
        deleteModal.show();
    });
});

// Confirm delete
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (!programTypeIdToDelete) return;
    fetch("{{ url('programs/types') }}/" + programTypeIdToDelete, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({_method: 'DELETE'})
    })
    .then(response => response.json())
    .then(data => {
        var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteProgramTypeModal'));
        deleteModal.hide();
        if (data.success) {
            var successModal = new bootstrap.Modal(document.getElementById('deleteSuccessModal'));
            successModal.show();
            // Optionally reload after modal closes
            document.getElementById('deleteSuccessModal').addEventListener('hidden.bs.modal', function () {
                window.location.reload();
            }, { once: true });
        } else {
            alert(data.error || 'Delete failed');
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});
</script>
@endpush
@endsection
