@extends('layouts.beneficiarieslayout')

@section('content')
<link rel="stylesheet" href="{{ asset('css/beneficiarydashboard.css') }}">

<!-- Styles moved to public/css/beneficiarydashboard.css -->

<style>
.dashboard-personal-header {
  background-image: url("{{ asset('img/beneficiaries-account-background.jpeg') }}");
  background-size: cover;
  background-position: center center;
}
</style>

<div class="container-fluid px-3 py-3">
    {{-- ===== TOP ROW ===== --}}
    <div class="row g-3 align-items-stretch mb-3">
        {{-- Personal Details --}}
        <div class="col-lg-6 col-md-6">
            <div class="card dashboard-card h-100 border-0">
                {{-- single Personal Details header with background --}}
                <div class="card-header dashboard-personal-header d-flex align-items-end gap-2">
                    <i class="bi bi-person-circle fs-4"></i>
                    <div>
                        <div class="fw-semibold">Personal Details</div>
                        <div class="small text-white-50">Your account summary</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if(!empty($beneficiary->avatar))
                            <img src="{{ asset('storage/' . $beneficiary->avatar) }}" alt="Avatar" class="personal-avatar shadow-sm">
                        @else
                            <div class="personal-avatar shadow-sm d-inline-flex align-items-center justify-content-center bg-primary text-white fw-bold" style="font-size:1.05rem;">
                                {{ strtoupper(substr($beneficiary->first_name ?? $beneficiary->username ?? '-', 0, 1)) }}
                            </div>
                        @endif
                         <div class="fw-bold fs-5 mt-2">{{ $beneficiary->first_name }} {{ $beneficiary->last_name }}</div>
                         <span class="badge bg-info text-dark">{{ $beneficiary->beneficiary_type }}</span>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li><i class="bi bi-envelope text-primary me-2"></i> <strong>Email:</strong> {{ $beneficiary->email ?? '-' }}</li>
                        <li><i class="bi bi-telephone text-primary me-2"></i> <strong>Phone:</strong> {{ $beneficiary->phone ?? '-' }}</li>
                        <li><i class="bi bi-calendar-event text-primary me-2"></i> <strong>Birthday:</strong> {{ $beneficiary->birthday ? \Carbon\Carbon::parse($beneficiary->birthday)->format('F d, Y') : '-' }}</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Document Status --}}
        <div class="col-lg-6 col-md-6">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-header d-flex align-items-center gap-2 bg-light rounded-top-4">
                    <i class="bi bi-folder2-open text-success fs-4"></i>
                    Document Status
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($documents as $doc)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>
                                    <i class="bi bi-file-earmark-text me-2 text-secondary"></i>
                                    {{ $doc->document_type }}
                                </span>

                                @php
                                    $rawStatus = trim((string)($doc->status ?? ''));
                                    $statusKey = strtolower($rawStatus);
                                    $badgeMap = [
                                        'verified' => 'bg-success text-white',
                                        'validated' => 'bg-success text-white',
                                        'pending' => 'bg-warning text-dark',
                                        'pending review' => 'bg-warning text-dark',
                                        'rejected' => 'bg-danger text-white',
                                        'action required' => 'bg-danger text-white',
                                    ];
                                    $badgeClass = $badgeMap[$statusKey] ?? 'bg-secondary text-white';
                                @endphp
                                <span class="badge status-badge {{ $badgeClass }}" data-status="{{ $statusKey }}">
                                    {{ $rawStatus !== '' ? ucfirst($rawStatus) : 'Unknown' }}
                                </span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted px-0">No documents uploaded.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SECOND ROW ===== --}}
    <div class="row g-3">
        {{-- Aid Application History --}}
        <div class="col-lg-8 col-md-12">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-header d-flex align-items-center gap-2 bg-light rounded-top-4">
                    <i class="bi bi-clock-history text-info fs-4"></i>
                    Aid Application History
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Program</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($applications as $i => $app)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <span class="fw-semibold">{{ $app->aidProgram->aid_program_name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if($app->status === 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($app->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($app->status === 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($app->status ?? 'N/A') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $app->created_at ? \Carbon\Carbon::parse($app->created_at)->format('M d, Y') : '-' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No applications found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-lg-4 col-md-12">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-header d-flex align-items-center gap-2 bg-light rounded-top-4">
                    <i class="bi bi-lightning-charge-fill text-danger fs-4"></i>
                    Quick Actions
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="{{ route('beneficiaries.applications') }}" class="btn btn-primary quick-action-btn w-100">
                        <i class="bi bi-plus-circle me-1"></i> Apply for Aid
                    </a>
                    <a href="{{ route('beneficiaries.documents') }}" class="btn btn-outline-secondary quick-action-btn w-100">
                        <i class="bi bi-folder2-open me-1"></i> View Documents
                    </a>
                    <a href="{{ route('beneficiaries.profile') }}" class="btn btn-danger quick-action-btn w-100">
                        <i class="bi bi-person-gear me-1"></i> Update Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
