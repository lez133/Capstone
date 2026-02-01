@extends('layouts.adminlayout')

@section('title', 'View Member')

@section('content')
<div class="container mt-4">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header d-flex justify-content-between align-items-center rounded-top-4"
             style="background: linear-gradient(90deg, #1976d2 0%, #42a5f5 100%); color: #fff;">
            <h5 class="mb-0 fw-semibold"><i class="fa fa-user-circle me-2"></i>Member Details</h5>
            @if($member->role === 'MSWD Representative')
                <a href="{{ route('members.mswd') }}" class="btn btn-light btn-sm rounded-pill px-3">Back to MSWD Members</a>
            @elseif($member->role === 'Barangay Representative')
                <a href="{{ route('members.brgy') }}" class="btn btn-light btn-sm rounded-pill px-3">Back to Barangay Representatives</a>
            @else
                <a href="{{ route('members.index') }}" class="btn btn-light btn-sm rounded-pill px-3">Back to Members</a>
            @endif
        </div>
        <div class="card-body py-4">
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="position-relative mb-3" style="width:100%; min-width:320px; min-height:320px;">
                        <!-- Full background image covering the content area behind profile picture, name, and role -->
                        <img src="{{ asset('img/profile-bg.png') }}"
                             alt="Profile Background"
                             class="position-absolute top-0 start-0 w-100 h-100"
                             style="object-fit: cover; z-index: 1; opacity: 0.5;">
                        <div class="position-relative d-flex flex-column align-items-center justify-content-center" style="z-index:2; min-height:320px;">
                            @if($member->profile_picture)
                                <img src="{{ asset('storage/' . $member->profile_picture) }}"
                                     alt="Profile Picture"
                                     class="rounded-circle shadow mb-3"
                                     style="width: 180px; height: 180px; object-fit: cover;">
                            @else
                                <div class="bg-secondary bg-opacity-25 text-dark rounded-circle d-flex align-items-center justify-content-center shadow mb-3"
                                     style="width: 180px; height: 180px;">
                                    <span class="fw-bold fs-5">No Image</span>
                                </div>
                            @endif
                            <div class="mt-2 px-3 py-2 rounded-3" style="background: rgba(255,255,255,0.85); display: inline-block;">
                                <h6 class="fw-bold fs-5 mb-1 text-dark">{{ $member->full_name }}</h6>
                                <span class="badge bg-primary bg-opacity-75 rounded-pill px-3 py-2">{{ $member->role }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row mb-3 g-3">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-secondary mb-2">Personal Information</h6>
                            <div class="mb-1"><strong>First Name:</strong> {{ $member->fname }}</div>
                            <div class="mb-1"><strong>Middle Name:</strong> {{ $member->mname ?? 'N/A' }}</div>
                            <div class="mb-1"><strong>Last Name:</strong> {{ $member->lname }}</div>
                            <div class="mb-1"><strong>Gender:</strong> {{ $member->gender }}</div>
                            <div class="mb-1"><strong>Birthday:</strong> {{ \Carbon\Carbon::parse($member->birthday)->format('F d, Y') }}</div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-secondary mb-2">Contact Information</h6>
                            <div class="mb-1"><strong>Email:</strong> <span class="text-primary">{{ $member->email }}</span></div>
                            <div class="mb-1"><strong>Contact Number:</strong> <span class="text-success">{{ $member->contact }}</span></div>
                            <div class="mb-1"><strong>Username:</strong> <span class="text-info">{{ $member->username }}</span></div>
                        </div>
                    </div>
                    <hr class="my-3 border border-primary border-2 opacity-50">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-secondary mb-2">Account Details</h6>
                            <div class="mb-1"><strong>Created By:</strong> <span class="text-warning">{{ $member->creator ? $member->creator->name : 'N/A' }}</span></div>
                            <div class="mb-1"><strong>Created At:</strong> <span class="text-secondary">{{ $member->created_at->format('F d, Y h:i A') }}</span></div>
                            <div class="mb-1"><strong>Updated At:</strong> <span class="text-secondary">{{ $member->updated_at->format('F d, Y h:i A') }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white border-0 rounded-bottom-4 text-end">
            <a href="{{ route('members.edit', Crypt::encrypt($member->id)) }}" class="btn btn-warning btn-sm rounded-pill px-3 me-2">
                <i class="fa fa-edit me-1"></i>Edit Member
            </a>
            <button type="button" class="btn btn-danger btn-sm rounded-pill px-3" id="delete-member-btn"
                data-bs-toggle="modal"
                data-bs-target="#deleteMemberModal"
                data-member-id="{{ Crypt::encrypt($member->id) }}"
                data-member-name="{{ $member->full_name }}">
                <i class="fa fa-trash me-1"></i>Delete Member
            </button>
        </div>
    </div>
</div>
@endsection

{{-- Modern Delete Modal --}}
<div class="modal fade" id="deleteMemberModal" tabindex="-1" aria-labelledby="deleteMemberModalLabel" aria-hidden="true">
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
        <p class="text-muted mb-4">Are you sure you want to delete this member?<br>This action cannot be undone.</p>
        <button type="button" class="btn btn-danger w-100 mb-2 rounded-pill" id="confirmDeleteBtn">
          <i class="fa fa-trash me-1"></i>Delete Member
        </button>
        <button type="button" class="btn btn-outline-secondary w-100 rounded-pill" data-bs-dismiss="modal">
          Cancel
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Success Modal --}}
<div class="modal fade" id="deleteSuccessModal" tabindex="-1" aria-labelledby="deleteSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 18px;">
      <div class="modal-header bg-success text-white border-0 rounded-top-4">
        <h5 class="modal-title" id="deleteSuccessModalLabel">
          <i class="fa fa-check-circle me-2"></i> Member Deleted
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="fs-5 mb-0">Member was successfully deleted.</p>
      </div>
      <div class="modal-footer bg-light justify-content-center border-0 rounded-bottom-4">
        <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
let memberIdToDelete = null;

document.getElementById('delete-member-btn').addEventListener('click', function() {
    memberIdToDelete = this.getAttribute('data-member-id');
    document.getElementById('delete-member-name').textContent = this.getAttribute('data-member-name');
});

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (!memberIdToDelete) return;
    fetch("{{ url('members/destroy') }}/" + memberIdToDelete, {
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
        if (data.success) {
            var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteMemberModal'));
            deleteModal.hide();
            var successModal = new bootstrap.Modal(document.getElementById('deleteSuccessModal'));
            successModal.show();

            // Redirect after modal closes based on role
            document.getElementById('deleteSuccessModal').addEventListener('hidden.bs.modal', function () {
                if (data.role === 'MSWD Representative') {
                    window.location.href = "{{ route('members.mswd') }}";
                } else if (data.role === 'Barangay Representative') {
                    window.location.href = "{{ route('members.brgy') }}";
                } else {
                    window.location.href = "{{ route('members.index') }}";
                }
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
