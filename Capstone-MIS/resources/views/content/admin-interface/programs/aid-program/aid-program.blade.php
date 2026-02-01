@extends('layouts.adminlayout')

@section('title', 'Aid Programs')

@section('content')

<link rel="stylesheet" href="{{ asset('css/AidAssistance.css') }}">
<!-- Choices.css -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<div class="container py-4">
    <div class="mb-3">
        <a href="{{ route('programs.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Programs
        </a>
    </div>

    <!-- Add Aid Program Button -->
    <div class="mb-4 d-flex flex-wrap gap-2 align-items-center">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAidProgramModal">
            <i class="fa fa-plus"></i> Add Aid Program
        </button>
        <button id="toggleViewBtn" class="btn btn-outline-dark ms-2">
            <i class="fa fa-list"></i> Toggle List/Card View
        </button>
        <div class="ms-auto w-100 w-md-auto mt-2 mt-md-0">
            <input type="text" id="searchAidProgram" class="form-control" placeholder="Search Aid Program...">
        </div>
    </div>

    <!-- Card View -->
    <div id="cardView" class="row g-4">
        @forelse($aidPrograms as $aidProgram)
            @php
                $backgroundImage = $aidProgram->background_image
                    ? asset('storage/' . $aidProgram->background_image)
                    : ($aidProgram->default_background
                        ? asset('img/' . $aidProgram->default_background)
                        : asset('img/default-placeholder.jpg'));
            @endphp

            <div class="col-md-4 col-sm-6 aid-program-item">
                <div class="card aid-program-card" style="background-image: url('{{ $backgroundImage }}'); position: relative;">
                    <div class="card-overlay">
                        <h5 class="aid-program-name">{{ $aidProgram->aid_program_name }}</h5>
                        <p>{{ $aidProgram->description }}</p>
                        <small>Program Type: {{ $aidProgram->programType->program_type_name ?? '-' }}</small>
                        <small class="d-block mt-2">Added: {{ $aidProgram->created_at->format('Y-m-d') }}</small>
                        <p><strong>Requirements:</strong>
                            @foreach($aidProgram->requirements as $req)
                                {{ $req->document_requirement }}@if(!$loop->last), @endif
                            @endforeach
                        </p>
                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('aid-programs.show', $aidProgram->id) }}" class="btn btn-outline-light btn-sm">
                                <i class="fa fa-eye"></i> View Program
                            </a>
                            <a href="{{ route('schedules.create', ['aid_program_id' => $aidProgram->id]) }}" class="btn btn-outline-warning btn-sm">
                                <i class="fa fa-calendar-plus"></i> Create Schedule
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center">No Aid Programs Found</div>
            </div>
        @endforelse
    </div>

    <!-- List View (hidden by default) -->
    <div id="listView" class="table-responsive mb-4" style="display:none;">
        <table class="table table-bordered table-hover align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Program Type</th>
                    <th>Requirements</th>
                    <th>Added</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aidPrograms as $aidProgram)
                    <tr class="aid-program-item">
                        <td class="fw-semibold aid-program-name">{{ $aidProgram->aid_program_name }}</td>
                        <td style="max-width: 250px;">{{ Str::limit($aidProgram->description, 80) }}</td>
                        <td>{{ $aidProgram->programType->program_type_name ?? '-' }}</td>
                        <td>
                            @foreach($aidProgram->requirements as $req)
                                <span class="badge bg-info text-dark mb-1">{{ $req->document_requirement }}</span>
                            @endforeach
                        </td>
                        <td>{{ $aidProgram->created_at->format('Y-m-d') }}</td>
                        <td class="text-center">
                            <a href="{{ route('aid-programs.show', $aidProgram->id) }}" class="btn btn-outline-primary btn-sm mb-1">
                                <i class="fa fa-eye"></i> View
                            </a>
                            <a href="{{ route('schedules.create', ['aid_program_id' => $aidProgram->id]) }}" class="btn btn-outline-warning btn-sm mb-1">
                                <i class="fa fa-calendar-plus"></i> Create Schedule
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No Aid Programs Found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Add Aid Program Modal -->
    <div class="modal fade" id="addAidProgramModal" tabindex="-1" aria-labelledby="addAidProgramModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addAidProgramModalLabel"><i class="fa fa-plus"></i> Add Aid Program (Optional: Create Schedule)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('aid-programs.store') }}" enctype="multipart/form-data" id="addAidProgramForm">
                    @csrf
                    <div class="modal-body">
                        <!-- Aid Program Fields -->
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
                            <div id="program-type-select-group">
                                <select name="program_type_id" id="program_type_id" class="form-select" required>
                                    <option value="" disabled selected>Select Program Type</option>
                                    @foreach($programTypes as $programType)
                                        <option value="{{ $programType->id }}">{{ $programType->program_type_name }}</option>
                                    @endforeach
                                </select>
                                <div class="input-group mt-2">
                                    <input type="text" id="newProgramType" class="form-control" placeholder="Add new program type">
                                    <button type="button" id="addProgramTypeBtn" class="btn btn-outline-success">Add</button>
                                </div>
                                <div id="programTypeSuccess" class="alert alert-success mt-2 d-none"></div>
                                <div id="programTypeError" class="alert alert-danger mt-2 d-none"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="background_image" class="form-label">Upload Custom Background</label>
                            <input type="file" name="background_image" id="background_image" class="form-control" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label for="default_background" class="form-label">Or Choose a Default Background</label>
                            <select name="default_background" id="default_background" class="form-select">
                                <option value="" selected>-- Select Default Background --</option>
                                <option value="default1.jpg">Pattern 1</option>
                                <option value="default2.jpg">Pattern 2</option>
                                <option value="default3.jpg">Pattern 3</option>
                            </select>
                            <div class="row mt-2" id="defaultBgPreviewRow">
                                <div class="col">
                                    <img src="{{ asset('img/default1.jpg') }}" alt="Pattern 1" class="img-thumbnail default-bg-thumb" data-value="default1.jpg" style="height:60px;cursor:pointer;">
                                </div>
                                <div class="col">
                                    <img src="{{ asset('img/default2.jpg') }}" alt="Pattern 2" class="img-thumbnail default-bg-thumb" data-value="default2.jpg" style="height:60px;cursor:pointer;">
                                </div>
                                <div class="col">
                                    <img src="{{ asset('img/default3.jpg') }}" alt="Pattern 3" class="img-thumbnail default-bg-thumb" data-value="default3.jpg" style="height:60px;cursor:pointer;">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Requirements</label>
                            <div class="accordion mb-2" id="requirementAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingRequirements">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRequirements" aria-expanded="false" aria-controls="collapseRequirements">
                                            Select and view all requirements
                                        </button>
                                    </h2>
                                    <div id="collapseRequirements" class="accordion-collapse collapse" aria-labelledby="headingRequirements" data-bs-parent="#requirementAccordion">
                                        <div class="accordion-body">
                                            <div id="requirement-checkboxes">
                                                @foreach($requirements as $req)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="requirements[]" value="{{ $req->id }}" id="req{{ $req->id }}">
                                                        <label class="form-check-label" for="req{{ $req->id }}">
                                                            <strong>{{ $req->document_requirement }}</strong>
                                                            @if(!empty($req->description))
                                                                <span class="text-muted d-block small">{{ $req->description }}</span>
                                                            @endif
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="input-group mt-2">
                                <input type="text" id="newRequirement" class="form-control" placeholder="Add new requirement">
                                <button type="button" id="addRequirementBtn" class="btn btn-outline-primary">Add</button>
                            </div>
                        </div>

                        <hr>

                        <!-- Create Schedule toggle -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="createScheduleNow" name="create_schedule_now" value="1">
                            <label class="form-check-label" for="createScheduleNow">Create schedule now for this program</label>
                        </div>

                        <!-- Schedule Fields (hidden unless toggle checked) -->
                        <div id="scheduleFields" style="display:none;">
                            <div class="mb-3">
                                <label for="beneficiary_type" class="form-label">Beneficiary Type</label>
                                <select name="beneficiary_type" id="beneficiary_type" class="form-select">
                                    <option value="">Select (optional)</option>
                                    <option value="senior">Senior Citizens</option>
                                    <option value="pwd">PWD</option>
                                    <option value="both">Both</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="barangay_ids" class="form-label">Select Barangays</label>
                                <select name="barangay_ids[]" id="barangay_ids" class="form-select" multiple>
                                    @foreach($barangays as $barangay)
                                        <option value="{{ $barangay->id }}">{{ $barangay->barangay_name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Select one or more barangays (required if beneficiary type is Senior or Both).</small>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="datetime-local" name="start_date" id="start_date" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="datetime-local" name="end_date" id="end_date" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Program & Schedule</button>
                    </div>
                </form>
            </div>
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

@endsection

@push('scripts')
<script>
// set correct routes and CSRF token before external JS loads
window.programTypeStoreRoute = "{{ route('program-types.store') }}";
window.requirementStoreRoute = "{{ route('requirements.store') }}";
window.csrfToken = "{{ csrf_token() }}";
</script>
<!-- add Choices.js script before your custom JS -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="{{ asset('js/aid-program.js') }}"></script>
@endpush

