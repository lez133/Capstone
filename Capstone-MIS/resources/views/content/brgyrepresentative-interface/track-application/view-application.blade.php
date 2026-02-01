@extends('layouts.brgylayout')

@section('content')
<div class="container py-4">
    <a href="{{ route('brgyrep.track-applications.index') }}" class="btn btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h2 class="fw-bold mb-2">{{ $aidProgram->aid_program_name }}</h2>
    <div class="mb-3">
        <span class="badge bg-primary">{{ $aidProgram->status ?? 'Ongoing' }}</span>
        <span class="ms-2">Sponsored by: {{ $aidProgram->sponsor ?? '-' }}</span>
    </div>
    <div class="mb-3">
        <strong>Beneficiary:</strong> {{ $beneficiary->first_name }} {{ $beneficiary->last_name }}
    </div>
    <div class="mb-3">
        <strong>Distribution Date:</strong>
        {{ \Carbon\Carbon::parse($aidProgram->start_date)->format('l, F d, Y') }}
        —
        {{ \Carbon\Carbon::parse($aidProgram->end_date)->format('l, F d, Y') }}
    </div>
    <h4 class="mt-4 mb-3">Requirements & Documents</h4>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Requirement</th>
                        <th>Status</th>
                        <th>Document</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requirements as $row)
                        <tr>
                            <td>{{ $row['requirement']->document_requirement ?? $row['requirement']->name }}</td>
                            <td>
                                <span class="badge
                                    @if($row['status'] === 'Validated' || $row['status'] === 'Received') bg-success
                                    @elseif($row['status'] === 'Rejected') bg-danger
                                    @elseif($row['status'] === 'Pending Review' || $row['status'] === 'Pending') bg-warning text-dark
                                    @elseif($row['status'] === 'Reviewed') bg-info
                                    @elseif($row['status'] === 'Waiting for Admin Approval') bg-primary
                                    @else bg-secondary
                                    @endif
                                ">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                            <td>
                                @if($row['document'])
                                    <a href="{{ route('brgyrep.track-applications.download', $row['document']->id) }}" class="btn btn-outline-primary btn-sm">
                                        Download
                                    </a>
                                @else
                                    <span class="text-muted">No document</span>
                                @endif
                            </td>
                            <td>
                                @if(
                                    $row['status'] === 'Pending Review' ||
                                    $row['status'] === 'Pending' ||
                                    $row['status'] === 'Not Submitted'
                                )
                                    <form method="POST" action="{{ route('brgyrep.track-applications.review', [$aidProgram->id, $beneficiary->id, $row['requirement']->id]) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-info btn-sm">
                                            Mark as Reviewed
                                        </button>
                                    </form>
                                @elseif($row['status'] === 'Reviewed')
                                    <span class="text-info">Waiting for Admin Approval</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
