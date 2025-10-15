@extends('layouts.beneficiarieslayout')

@section('content')
<link rel="stylesheet" href="{{ asset('css/documents.css') }}">
<div class="container-fluid px-0 px-md-2">
    <div class="mb-4">
        <h2 class="fw-bold mb-1" style="font-size:2rem;">
            <i class="bi bi-upload text-primary me-2"></i>
            <span style="border-bottom:4px solid #8b5cf6; padding-bottom:2px;">Documents</span>
        </h2>
        <p class="text-muted mb-3">Upload and verify your required documents</p>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 stat-card bg-success-subtle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-success">Validated</span>
                        <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                    </div>
                    <h3 class="fw-bold mb-1 text-success">{{ $stats['validated'] }}</h3>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar bg-success" style="width:{{ $stats['completion'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 stat-card bg-warning-subtle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-warning">Pending Review</span>
                        <i class="bi bi-clock-history fs-4 text-warning"></i>
                    </div>
                    <h3 class="fw-bold mb-1 text-warning">{{ $stats['pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 stat-card bg-danger-subtle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-danger">Action Required</span>
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                    </div>
                    <h3 class="fw-bold mb-1 text-danger">{{ $stats['action_required'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 stat-card bg-purple-subtle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-purple">Completion</span>
                        <i class="bi bi-file-earmark-check fs-4 text-purple"></i>
                    </div>
                    <h3 class="fw-bold mb-1 text-purple">{{ $stats['completion'] }}%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Add above document cards -->
    <div class="mb-3 text-end">
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#submitDocumentModal">
            <i class="bi bi-plus-lg me-1"></i> Submit Document
        </button>
    </div>

    <!-- Modal for submitting document -->
    <div class="modal fade" id="submitDocumentModal" tabindex="-1" aria-labelledby="submitDocumentModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="POST" action="{{ route('beneficiaries.documents.submit') }}" enctype="multipart/form-data" class="modal-content">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="submitDocumentModalLabel">Submit Document</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="document_type" class="form-label">Document Type</label>
              <input type="text" class="form-control" id="document_type" name="document_type" required placeholder="e.g. PWD ID Card, Medical Certificate">
            </div>
            <div class="mb-3">
              <label for="document_file" class="form-label">Upload Document</label>
              <input type="file" class="form-control" id="document_file" name="document_file" accept="image/*,.pdf" required>
              <small class="text-muted">Accepted: Image or PDF. Images will be converted to PDF.</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success rounded-pill px-4">Submit</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Document Cards -->
    <div class="mb-4">
        @forelse($documents as $document)
            <div class="card shadow-sm border-0 mb-4 document-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="fw-bold fs-5">{{ $document->document_type }}</span>
                            <span class="badge bg-primary-subtle text-primary ms-2">Identification</span>
                            <span class="text-muted ms-2">DOC-{{ $document->id }}</span>
                        </div>
                        <span class="badge
                            @if($document->status === 'Validated') bg-success-subtle text-success
                            @elseif($document->status === 'Pending Review') bg-warning-subtle text-warning
                            @else bg-danger-subtle text-danger
                            @endif
                            px-3 py-2 fw-semibold">{{ $document->status }}</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-file-earmark-pdf fs-4 text-primary me-2"></i>
                        <span class="fw-semibold">{{ basename($document->file_path) }}</span>
                        <span class="ms-auto text-muted">
                            {{ Storage::exists($document->file_path) ? round(Storage::size($document->file_path)/1024/1024, 2) . ' MB' : '' }}
                        </span>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col">
                            <div class="fw-semibold text-muted">Uploaded</div>
                            <div class="text-dark">{{ $document->uploaded_at ? \Carbon\Carbon::parse($document->uploaded_at)->format('m/d/Y') : '-' }}</div>
                        </div>
                        <div class="col">
                            <div class="fw-semibold text-muted">Validated</div>
                            <div class="text-success">
                                {{ $document->status === 'Validated' ? \Carbon\Carbon::parse($document->updated_at)->format('m/d/Y') : '-' }}
                            </div>
                        </div>
                        <div class="col">
                            <div class="fw-semibold text-muted">Expires</div>
                            <div class="text-purple">-</div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('beneficiaries.documents.download', $document->id) }}" class="btn btn-light border rounded-pill px-4">
                            <i class="bi bi-download me-1"></i> Download
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">No documents submitted yet.</div>
        @endforelse
    </div>
</div>
@endsection
