@extends('layouts.adminlayout')

@section('title', 'Beneficiary Files')

@section('content')
<style>
    .modern-table th, .modern-table td {
        vertical-align: middle !important;
    }
    .modern-table th {
        background: #f8fafc;
        font-weight: 600;
        color: #22223b;
        border-bottom: 2px solid #e9ecef;
    }
    .modern-table tr {
        transition: background 0.2s;
    }
    .modern-table tr:hover {
        background: #f1f3f9;
    }
    .badge-status {
        font-size: 0.95em;
        padding: 0.5em 0.9em;
        border-radius: 1em;
        letter-spacing: 0.02em;
    }
    .action-btns .btn {
        margin-bottom: 0.25rem;
        min-width: 90px;
    }
    .action-btns form {
        display: inline;
    }
    .table-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #22223b;
        margin-bottom: 0.5rem;
    }
    .table-subtitle {
        color: #4a4e69;
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
</style>
<div class="container py-4">
    <a href="{{ route('document.program.type.selector', ['barangay_id' => request('barangay_id'), 'beneficiary_type' => $beneficiary->beneficiary_type]) }}" class="btn btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    <div class="table-title">
        Files for Beneficiary: <span class="fw-bold">{{ $beneficiary->last_name }}, {{ $beneficiary->first_name }}</span>
        <span class="text-muted">({{ $beneficiary->beneficiary_type }})</span>
    </div>
    <div class="table-subtitle">
        <i class="bi bi-geo-alt-fill text-primary"></i>
        Barangay: <span class="text-primary">{{ $barangay->barangay_name }}</span>
    </div>

    {{-- Success message --}}
    @if(session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Notification message if beneficiary has been notified via SMS --}}
    @foreach($documents as $doc)
        @if(session('doc_success_' . $doc->id))
            <div class="alert alert-success">
                <i class="bi bi-chat-dots-fill"></i>
                {{ session('doc_success_' . $doc->id) }} (SMS notification sent to beneficiary)
            </div>
        @endif
    @endforeach

    @php
        // Preload all APRB records for this beneficiary, keyed by beneficiary_document_id
        $aprb = \App\Models\AidProgramRequirementBeneficiary::with('aidProgram')
            ->where('beneficiary_id', $beneficiary->id)
            ->whereIn('beneficiary_document_id', $documents->pluck('id'))
            ->get()
            ->groupBy('beneficiary_document_id');
    @endphp

    <!-- Filter Form -->
    <form method="GET" class="mb-4">
        <input type="hidden" name="barangay_id" value="{{ request('barangay_id') }}">
        <input type="hidden" name="beneficiary_id" value="{{ request('beneficiary_id') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All</option>
                    <option value="Validated" {{ request('status') == 'Validated' ? 'selected' : '' }}>Validated</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="Disabled" {{ request('status') == 'Disabled' ? 'selected' : '' }}>Disabled</option>
                    <option value="Pending Review" {{ request('status') == 'Pending Review' ? 'selected' : '' }}>Pending Review</option>
                    <option value="Submitted" {{ request('status') == 'Submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="reviewed, waiting for admin approval" {{ request('status') == 'reviewed, waiting for admin approval' ? 'selected' : '' }}>reviewed, waiting for admin approval</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="type" class="form-label">Document Type</label>
                <input type="text" name="type" id="type" class="form-control" value="{{ request('type') }}" placeholder="e.g. ID, Certificate">
            </div>
            <div class="col-md-3">
                <label for="used_for" class="form-label">Used For</label>
                @php
                    // Get all unique aid program names in "Used For" dropdown
                    $aidProgramsInUse = collect();
                    foreach ($documents as $doc) {
                        if (isset($aprb[$doc->id])) {
                            foreach ($aprb[$doc->id] as $row) {
                                if ($row->aidProgram) {
                                    $aidProgramsInUse->push($row->aidProgram->aid_program_name);
                                }
                            }
                        }
                    }
                    $aidProgramsInUse = $aidProgramsInUse->unique()->sort()->values();
                @endphp
                <select name="used_for" id="used_for" class="form-select">
                    <option value="">All</option>
                    @foreach($aidProgramsInUse as $aidName)
                        <option value="{{ $aidName }}" {{ request('used_for') == $aidName ? 'selected' : '' }}>{{ $aidName }}</option>
                    @endforeach
                    <option value="Personal File" {{ request('used_for') == 'Personal File' ? 'selected' : '' }}>Personal File</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </div>
    </form>

    @php
        // Apply filters
        $filteredDocuments = $documents->filter(function($doc) use ($aprb) {
            $status = request('status');
            $type = request('type');
            $usedFor = request('used_for');
            $match = true;

            // Status filter (case-insensitive, supports both reviewed, waiting for admin approval and Reviewed, Waiting for Admin Approval)
            if ($status && strcasecmp($doc->status, $status) !== 0) $match = false;
            if ($type && stripos($doc->document_type, $type) === false) $match = false;

            if ($usedFor) {
                $usedForMatch = false;
                if (isset($aprb[$doc->id])) {
                    foreach ($aprb[$doc->id] as $row) {
                        if ($row->aidProgram && stripos($row->aidProgram->aid_program_name, $usedFor) !== false) {
                            $usedForMatch = true;
                            break;
                        }
                    }
                }
                if (!$usedForMatch && !(stripos('Personal File', $usedFor) !== false && empty($aprb[$doc->id]))) {
                    $match = false;
                }
            }

            return $match;
        });
    @endphp

    @if($filteredDocuments->isEmpty())
        <div class="alert alert-warning rounded-3 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill"></i>
            No files/documents found for this beneficiary.
        </div>
    @else
        <div class="table-responsive rounded-3 shadow-sm">
            <table class="table modern-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Document Type</th>
                        <th>Description</th>
                        <th>Used For</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th>File</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($filteredDocuments as $index => $doc)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $doc->document_type ?? 'N/A' }}</td>
                            <td>{{ $doc->description ?? '-' }}</td>
                            <td>
                                @php
                                    $usedFor = [];
                                    if (isset($aprb[$doc->id])) {
                                        foreach ($aprb[$doc->id] as $row) {
                                            if ($row->aidProgram) {
                                                $usedFor[] = $row->aidProgram->aid_program_name;
                                            }
                                        }
                                    }
                                @endphp
                                @if(count($usedFor))
                                    @foreach($usedFor as $programName)
                                        <span class="badge bg-info text-dark mb-1">{{ $programName }}</span>
                                    @endforeach
                                @else
                                    <span class="badge bg-secondary">Personal File</span>
                                @endif
                            </td>
                            <td>
                                @if($doc->status === 'Validated')
                                    <span class="badge badge-status bg-success">Validated</span>
                                @elseif($doc->status === 'Rejected')
                                    <span class="badge badge-status bg-danger">Rejected</span>
                                @elseif($doc->status === 'Disabled')
                                    <span class="badge badge-status bg-secondary">Disabled</span>
                                @else
                                    <span class="badge badge-status bg-warning text-dark">{{ $doc->status ?? 'Pending' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($doc->created_at)
                                    <span class="text-nowrap">{{ $doc->created_at->format('M d, Y h:i A') }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($doc->file_path)
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                @else
                                    <span class="text-muted">No file</span>
                                @endif
                            </td>
                            <td class="text-center action-btns">
                                <a href="{{ route('admin.documents.download', $doc->id) }}" class="btn btn-outline-success btn-sm" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                                @if($doc->status !== 'Validated' && $doc->status !== 'Disabled')
                                    <form method="POST" action="{{ route('admin.documents.verify', $doc->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary btn-sm" title="Verify">
                                            <i class="bi bi-check2-circle"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($doc->status !== 'Rejected')
                                    <button type="button" class="btn btn-outline-danger btn-sm" title="Reject" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $doc->id }}">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                    <!-- Modal -->
                                    <div class="modal fade" id="rejectModal{{ $doc->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $doc->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('admin.documents.reject', $doc->id) }}">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="rejectModalLabel{{ $doc->id }}">Reject Document</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="reason{{ $doc->id }}" class="form-label">Reason for rejection</label>
                                                            <textarea name="reason" id="reason{{ $doc->id }}" class="form-control" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($doc->status !== 'Disabled')
                                    <form method="POST" action="{{ route('admin.documents.disable', $doc->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning btn-sm" title="Disable">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($doc->status === 'Disabled')
                                    <form method="POST" action="{{ route('admin.documents.enable', $doc->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-info btn-sm" title="Enable">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
