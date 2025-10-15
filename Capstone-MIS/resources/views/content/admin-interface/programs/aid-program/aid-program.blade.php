@extends('layouts.adminlayout')

@section('title', 'Aid Programs')

@section('content')

<link rel="stylesheet" href="{{ asset('css/AidAssistance.css') }}">

<div class="container py-4">
    <div class="mb-3">
        <a href="{{ route('programs.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Programs
        </a>
    </div>

    <!-- Add Aid Program Button -->
    <div class="mb-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAidProgramModal">
            <i class="fa fa-plus"></i> Add Aid Program
        </button>
    </div>

    <!-- Aid Program Cards -->
    <div class="row g-4">
        @forelse($aidPrograms as $aidProgram)
            @php
                $backgroundImage = $aidProgram->background_image
                    ? asset('storage/' . $aidProgram->background_image)
                    : ($aidProgram->default_background
                        ? asset('img/' . $aidProgram->default_background)
                        : asset('img/default-placeholder.jpg'));
            @endphp

            <div class="col-md-4 col-sm-6">
                <a href="{{ route('aid-programs.show', $aidProgram->id) }}" class="card aid-program-card" style="background-image: url('{{ $backgroundImage }}');">
                    <div class="card-overlay">
                        <h5>{{ $aidProgram->aid_program_name }}</h5>
                        <p>{{ $aidProgram->description }}</p>
                        <small>Program Type: {{ $aidProgram->programType->program_type_name }}</small>
                        <small class="d-block mt-2">Added: {{ $aidProgram->created_at->format('Y-m-d') }}</small>
                        <p><strong>Requirements:</strong>
                            @foreach($aidProgram->requirements as $req)
                                {{ $req->document_requirement }}@if(!$loop->last), @endif
                            @endforeach
                        </p>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center">No Aid Programs Found</div>
            </div>
        @endforelse
    </div>
</div>

<!-- Add Aid Program Modal -->
<div class="modal fade" id="addAidProgramModal" tabindex="-1" aria-labelledby="addAidProgramModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addAidProgramModalLabel"><i class="fa fa-plus"></i> Add Aid Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('aid-programs.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="aid_program_name" class="form-label">Aid Program Name</label>
                        <input type="text" name="aid_program_name" id="aid_program_name" class="form-control" placeholder="Enter Aid Program Name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter Description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="program_type_id" class="form-label">Program Type</label>
                        <select name="program_type_id" id="program_type_id" class="form-select" required>
                            <option value="" disabled selected>Select Program Type</option>
                            @foreach($programTypes as $programType)
                                <option value="{{ $programType->id }}">{{ $programType->program_type_name }}</option>
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
                            <option value="" disabled selected>Select Default Background</option>
                            <option value="default1.jpg">Default Background 1</option>
                            <option value="default2.jpg">Default Background 2</option>
                            <option value="default3.jpg">Default Background 3</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Requirements</label>
                        <div id="requirement-checkboxes" class="mb-2">
                            @foreach($requirements as $req)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="requirements[]" value="{{ $req->id }}" id="req{{ $req->id }}">
                                    <label class="form-check-label" for="req{{ $req->id }}">
                                        {{ $req->document_requirement }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="input-group mt-2">
                            <input type="text" id="newRequirement" class="form-control" placeholder="Add new requirement">
                            <button type="button" id="addRequirementBtn" class="btn btn-outline-primary">Add</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
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
      <div class="modal-body" id="requirementErrorModalBody">
        <!-- Error message will be injected here -->
      </div>
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
                showRequirementError(data.message || 'Could not add requirement.');
            }
        })
        .catch(() => showRequirementError('Server error. Please try again.'));
    }
};

// Success message function
function showRequirementSuccess(message) {
    let alertDiv = document.getElementById('requirement-success-alert');
    if (!alertDiv) {
        alertDiv = document.createElement('div');
        alertDiv.id = 'requirement-success-alert';
        alertDiv.className = 'alert alert-success mt-2';
        document.getElementById('requirement-checkboxes').parentNode.insertBefore(alertDiv, document.getElementById('requirement-checkboxes'));
    }
    alertDiv.textContent = message;
    setTimeout(() => {
        alertDiv.remove();
    }, 2000);
}

// Error modal function
function showRequirementError(message) {
    document.getElementById('requirementErrorModalBody').innerHTML = message;
    var errorModal = new bootstrap.Modal(document.getElementById('requirementErrorModal'));
    errorModal.show();
}
</script>
@endsection

