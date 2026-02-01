@extends('layouts.brgylayout')

@section('title', 'Submitted Documents')

@section('content')

<style>
/* Responsive Table Enhancements */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
}
@media (max-width: 768px) {
    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        background: #fff;
        padding-bottom: 5px;
    }
    table.table th,
    table.table td {
        padding: 8px 8px !important;
        font-size: 13px;
        white-space: nowrap;
    }
    .badge {
        font-size: 12px;
        padding: 6px 8px;
    }
    .btn-sm {
        padding: 4px 8px;
        font-size: 12px;
        width: 100%;
        margin-bottom: 4px;
    }
    .table thead {
        display: none;
    }
    .table tr {
        display: block;
        margin-bottom: 12px;
        border-bottom: 1px solid #eee;
    }
    .table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: none;
        width: 100%;
    }
    .table td:before {
        content: attr(data-label);
        font-weight: 600;
        color: #495057;
        flex-basis: 50%;
        text-align: left;
    }
}
table.table-hover tbody tr td {
    vertical-align: middle;
}
@supports (-webkit-touch-callout: none) {
    body {
        touch-action: manipulation;
    }
}
</style>

<div class="container-fluid py-3 px-2 px-md-4">

    <a href="{{ route('brgyrep.submit-document.create') }}" class="btn btn-secondary mt-3 w-100 w-md-auto">
        <i class="fas fa-arrow-left me-2"></i>Back to Submissions
    </a>

    <div class="card border-0 rounded-3 shadow-sm mt-3">
        <div class="card-body p-2 p-md-4">

            <h4 class="fw-bold mb-3 fs-6 fs-md-4">
                <i class="fas fa-folder-open me-2"></i>
                Submitted Documents for {{ $beneficiary->first_name }} {{ $beneficiary->last_name }}
            </h4>

            <p class="text-muted mb-3 small">
                Beneficiary Type: <strong>{{ $beneficiary->beneficiary_type ?? 'N/A' }}</strong>
            </p>

            <div class="table-responsive">
                <table class="table table-hover responsive-table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Document Type</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Date Submitted</th>
                            <th>File</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                            @php
                                $submittedTo = $doc->aid_type;
                                if (empty($submittedTo)) {
                                    try {
                                        $arb = \App\Models\AidProgramRequirementBeneficiary::where('beneficiary_document_id', $doc->id)
                                            ->with('aidProgram')->first();
                                        if ($arb && $arb->aidProgram) {
                                            $submittedTo = $arb->aidProgram->aid_program_name ?? $arb->aidProgram->name;
                                        }
                                    } catch (\Throwable $e) {}
                                }
                                if (empty($submittedTo)) {
                                    $submittedTo = $doc->description ?: 'Personal Document';
                                }
                                $statusClass = match(strtolower($doc->status)) {
                                    'pending review' => 'badge bg-warning text-dark',
                                    'reviewed' => 'badge bg-primary',
                                    'waiting for admin approval' => 'badge bg-info text-dark',
                                    'completed' => 'badge bg-success',
                                    'rejected' => 'badge bg-danger',
                                    default => 'badge bg-secondary'
                                };
                                $statusText = $doc->status === 'reviewed'
                                    ? 'Reviewed (Waiting for Admin Approval)'
                                    : ucfirst($doc->status);
                                $arb = \App\Models\AidProgramRequirementBeneficiary::where('beneficiary_document_id', $doc->id)->first();
                                $dateSubmitted = $arb && $arb->created_at
                                    ? \Carbon\Carbon::parse($arb->created_at)->format('M d, Y h:i A')
                                    : ($doc->uploaded_at ? \Carbon\Carbon::parse($doc->uploaded_at)->format('M d, Y h:i A') : 'N/A');
                            @endphp

                            <tr>
                                <td data-label="Document Type">{{ $doc->document_type }}</td>
                                <td data-label="Description">{{ $submittedTo }}</td>
                                <td data-label="Status"><span class="{{ $statusClass }}">{{ $statusText }}</span></td>
                                <td data-label="Date Submitted">{{ $dateSubmitted }}</td>
                                <td data-label="File">
                                    <a href="{{ route('brgyrep.documents.view', $doc->id) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                </td>
                                <td data-label="Action">
                                    <form method="POST" action="{{ route('brgyrep.documents.review', $doc->id) }}">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-sm btn-outline-primary"
                                            @if(str_contains(strtolower($doc->status), 'reviewed') || str_contains(strtolower($doc->status), 'waiting for admin approval')) disabled @endif>
                                            <i class="fas fa-check me-1"></i>
                                            @if(str_contains(strtolower($doc->status), 'reviewed') || str_contains(strtolower($doc->status), 'waiting for admin approval'))
                                                Waiting for Admin Approval
                                            @else
                                                Mark as Reviewed
                                            @endif
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No documents submitted by this beneficiary.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection
