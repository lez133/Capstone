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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

