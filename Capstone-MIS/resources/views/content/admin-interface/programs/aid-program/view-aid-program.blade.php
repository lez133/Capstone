@extends('layouts.adminlayout')

@section('title', 'View Aid Program')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .card-header {
        background-color: #007bff;
        color: white;
        font-weight: bold;
    }
    .card-body img {
        max-width: 100%;
        border-radius: 10px;
        margin-bottom: 15px;
    }
</style>

<div class="container py-4">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('aid-programs.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Aid Programs
        </a>
    </div>

    <!-- Aid Program Details -->
    <div class="card">
        <div class="card-header">
            <h5>{{ $aidProgram->aid_program_name }}</h5>
        </div>
        <div class="card-body">
            <!-- Background Image -->
            @if($aidProgram->background_image)
                <img src="{{ asset('storage/' . $aidProgram->background_image) }}" alt="Background Image">
            @elseif($aidProgram->default_background)
                <img src="{{ asset('img/' . $aidProgram->default_background) }}" alt="Default Background">
            @else
                <img src="{{ asset('img/default-placeholder.jpg') }}" alt="Placeholder Image">
            @endif

            <!-- Program Details -->
            <p><strong>Description:</strong> {{ $aidProgram->description }}</p>
            <p><strong>Program Type:</strong> {{ $aidProgram->programType->program_type_name }}</p>
            <p><strong>Created At:</strong> {{ $aidProgram->created_at->format('Y-m-d') }}</p>
        </div>
    </div>

    <!-- Update and Delete Buttons -->
    <div class="mt-4">
        <!-- Update Button -->
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateAidProgramModal">
            <i class="fa fa-edit"></i> Update
        </button>

        <!-- Delete Button -->
        <form method="POST" action="{{ route('aid-programs.destroy', $aidProgram->id) }}" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this Aid Program?')">
                <i class="fa fa-trash"></i> Delete
            </button>
        </form>
    </div>
</div>

<!-- Update Aid Program Modal -->
<div class="modal fade" id="updateAidProgramModal" tabindex="-1" aria-labelledby="updateAidProgramModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="updateAidProgramModalLabel"><i class="fa fa-edit"></i> Update Aid Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('aid-programs.update', $aidProgram->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="aid_program_name" class="form-label">Aid Program Name</label>
                        <input type="text" name="aid_program_name" id="aid_program_name" class="form-control" value="{{ $aidProgram->aid_program_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3" required>{{ $aidProgram->description }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="program_type_id" class="form-label">Program Type</label>
                        <select name="program_type_id" id="program_type_id" class="form-select" required>
                            @foreach($programTypes as $programType)
                                <option value="{{ $programType->id }}" {{ $aidProgram->program_type_id == $programType->id ? 'selected' : '' }}>
                                    {{ $programType->program_type_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="background_image" class="form-label">Upload Custom Background</label>
                        <input type="file" name="background_image" id="background_image" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="default_background" class="form-label">Or Select Default Background</label>
                        <select name="default_background" id="default_background" class="form-select">
                            <option value="" disabled>Select Default Background</option>
                            <option value="default1.jpg" {{ $aidProgram->default_background == 'default1.jpg' ? 'selected' : '' }}>Default Background 1</option>
                            <option value="default2.jpg" {{ $aidProgram->default_background == 'default2.jpg' ? 'selected' : '' }}>Default Background 2</option>
                            <option value="default3.jpg" {{ $aidProgram->default_background == 'default3.jpg' ? 'selected' : '' }}>Default Background 3</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Requirements</label>
                        <div id="requirement-checkboxes" class="mb-2">
                            @foreach($requirements as $req)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="requirements[]" value="{{ $req->id }}"
                                        id="req{{ $req->id }}"
                                        {{ $aidProgram->requirements->contains($req->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="req{{ $req->id }}">
                                        {{ $req->document_requirement }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <input type="text" id="newRequirement" class="form-control" placeholder="Add new requirement" style="max-width: 400px;">
                            <button type="button" id="addRequirementBtn" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div id="requirement-success-alert" class="alert alert-success mt-2 d-none"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Requirement Error Modal -->
<div class="modal fade" id="requirementErrorModal" tabindex="-1" aria-labelledby="requirementErrorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="requirementErrorModalLabel">Error Adding Requirement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="requirementErrorModalBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('addRequirementBtn').onclick = function() {
    const newReqInput = document.getElementById('newRequirement');
    const newReq = newReqInput.value.trim();
    if (newReq) {
        fetch("{{ route('requirements.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ document_requirement: newReq })
        })
        .then(res => res.json())
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
            } else if (data.errors) {
                showRequirementError(Object.values(data.errors).join('<br>'));
            } else {
                showRequirementError(data.message || 'Requirement already exists or could not be added.');
            }
        })
        .catch(() => showRequirementError('Unable to add requirement. Please check your connection or try again.'));
    }
};

function showRequirementSuccess(message) {
    let alertDiv = document.getElementById('requirement-success-alert');
    alertDiv.textContent = message;
    alertDiv.classList.remove('d-none');
    setTimeout(() => {
        alertDiv.classList.add('d-none');
    }, 2000);
}

function showRequirementError(message) {
    document.getElementById('requirementErrorModalBody').innerHTML = message;
    var errorModal = new bootstrap.Modal(document.getElementById('requirementErrorModal'));
    errorModal.show();
}
</script>
@endsection
