@extends('layouts.beneficiarieslayout')

@section('content')
@php
    // Check if all requirements for this aid program are already submitted
    $allSubmitted = true;
    foreach($requirements as $req) {
        $submitted = \App\Models\AidProgramRequirementBeneficiary::where('beneficiary_id', auth()->guard('beneficiary')->user()->id)
            ->where('aid_program_id', $aidProgramId)
            ->where('requirement_id', $req->id)
            ->whereNotNull('beneficiary_document_id')
            ->exists();
        if (!$submitted) {
            $allSubmitted = false;
            break;
        }
    }

    // Determine if this specific requirement is already submitted for this beneficiary
    $alreadySubmitted = \App\Models\AidProgramRequirementBeneficiary::where('beneficiary_id', auth()->guard('beneficiary')->user()->id)
        ->where('aid_program_id', $aidProgramId)
        ->where('requirement_id', $requirement->id)
        ->whereNotNull('beneficiary_document_id')
        ->exists();

    // If already submitted, eager load the submission record and document for display
    $arb = $alreadySubmitted
        ? \App\Models\AidProgramRequirementBeneficiary::where('beneficiary_id', auth()->guard('beneficiary')->user()->id)
            ->where('aid_program_id', $aidProgramId)
            ->where('requirement_id', $requirement->id)
            ->whereNotNull('beneficiary_document_id')
            ->first()
        : null;

    $submittedDoc = $arb ? \App\Models\BeneficiaryDocument::find($arb->beneficiary_document_id) : null;
    $fileUrl = $submittedDoc ? \Illuminate\Support\Facades\Storage::url($submittedDoc->file_path) : null;
    $ext = $fileUrl ? pathinfo($fileUrl, PATHINFO_EXTENSION) : null;
@endphp

<div class="container py-4">
    {{-- Back button: returns to the previous page --}}
    <a href="{{ route('beneficiaries.applications') }}" class="btn btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>

    <h4 class="mb-3">
        Submit Document for:
        <span class="text-primary">
            {{ $requirement->aidPrograms->first()->aid_program_name ?? 'N/A' }}
        </span>
    </h4>
    <div class="mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold mb-2 text-dark">
                    Aid Program:
                    <span class="text-primary">
                        {{ $requirement->aidPrograms->first()->aid_program_name ?? 'N/A' }}
                    </span>
                </h5>
                <div class="mb-2">
                    <strong>All Requirements for this Program:</strong>
                    <form id="requirementSelectForm" method="GET" action="">
                        <select name="requirement_id" id="requirement_id" class="form-select mb-3"
                            onchange="if(this.value){ window.location='{{ url()->current() }}?requirement_id='+this.value; }">
                            @foreach($requirements as $req)
                                <option value="{{ $req->id }}" @if($req->id == $requirement->id) selected @endif>
                                    {{ $req->document_requirement }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- show submit form only when the specific requirement has NOT been submitted yet --}}
    @if(!$allSubmitted && !$alreadySubmitted)
        <form action="{{ route('beneficiary.submit-document.store', [$aidProgramId, $requirement->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- ensure controller can return to this same page after submit --}}
            <input type="hidden" name="return_url" value="{{ url()->full() }}">

            <div class="mb-3">
                <label class="form-label fw-semibold">Choose a validated document (if any):</label>
                <select name="validated_document_id" class="form-select">
                    <option value="">-- None, I want to upload a new document --</option>
                    @foreach($validatedDocs as $doc)
                        <option value="{{ $doc->id }}">
                            {{ $doc->document_type }} (Uploaded: {{ $doc->uploaded_at ? $doc->uploaded_at->format('Y-m-d') : 'N/A' }})
                        </option>
                    @endforeach
                </select>
                <div class="form-text">If you select a validated document, it will still be reviewed for this program's requirement.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Or upload a new document:</label>
                <input type="file" name="document_file" class="form-control" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx">
                <div class="form-text">Accepted formats: JPEG, PNG, PDF, DOC, DOCX (Max 5MB)</div>
            </div>

            <button type="submit" class="btn btn-primary">Submit Document</button>
        </form>
    @endif

    @if($alreadySubmitted)
        {{-- use $submittedDoc, $fileUrl and $ext computed earlier --}}
        <div class="text-center mt-3">
            @if($submittedDoc)
                <a href="{{ route('beneficiaries.documents.view', $submittedDoc->id) }}" target="_blank" class="btn btn-outline-primary">
                    <i class="bi bi-eye me-1"></i> View Document
                </a>
                <a href="{{ route('beneficiaries.documents.download', $submittedDoc->id) }}" class="btn btn-outline-success ms-2">
                    <i class="bi bi-download me-1"></i> Download
                </a>
            @else
                <div class="alert alert-warning">Submitted file not found.</div>
            @endif
        </div>

        @if($submittedDoc && !in_array(strtolower($submittedDoc->status), ['validated', 'reviewed']))
            {{-- Replace inline confirm with modal trigger + hidden form so UI remains intact --}}
            <div class="mt-3 text-center">
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelSubmissionModal">
                    Cancel Submission
                </button>
            </div>

            {{-- Hidden delete form submitted by modal confirm --}}
            <form id="cancelSubmissionForm" method="POST" action="{{ route('beneficiaries.documents.destroy', $submittedDoc->id) }}" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @elseif($submittedDoc)
            <div class="mt-3 text-center">
                <span class="badge bg-success">This document is {{ ucfirst($submittedDoc->status) }} and cannot be cancelled.</span>
            </div>
        @endif
    @endif
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelSubmissionModal" tabindex="-1" aria-labelledby="cancelSubmissionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-3">
      <div class="modal-body text-center p-4">
        <div class="mb-3">
          <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:2rem;"></i>
        </div>
        <h5 class="fw-bold">Are you sure?</h5>
        <p class="text-muted mb-3">Are you sure you want to cancel and delete this submission? This action cannot be undone.</p>

        <div class="d-grid gap-2">
          <button type="button" id="confirmCancelSubmissionBtn" class="btn btn-danger btn-lg">Delete Submission</button>
          <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function () {
    const confirmBtn = document.getElementById('confirmCancelSubmissionBtn');
    const form = document.getElementById('cancelSubmissionForm');

    if (confirmBtn && form) {
        confirmBtn.addEventListener('click', function () {
            // disable button to prevent double submits
            confirmBtn.disabled = true;
            confirmBtn.classList.add('opacity-75');
            form.submit();
        });
    }
})();
</script>
@endpush
@endsection
