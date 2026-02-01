{{-- filepath: resources/views/content/brgyrepresentative-interface/track-application/track-applications.blade.php --}}
@extends('layouts.brgylayout')

@section('title', 'Track Beneficiary Applications')

@section('content')
<div class="container-fluid px-0 px-md-2">
    <h2 class="fw-bold mb-4">Tracked Applications</h2>
    <a href="{{ route('brgyrep.track-applications.index') }}" class="btn btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
    @forelse($programsData as $data)
        @php $program = $data['program']; @endphp
        <div class="mb-5">
            <div class="card aid-card shadow-sm border-0 h-100 mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="fw-bold mb-0">{{ $program->aid_program_name }}</h4>
                        <span class="badge bg-light text-primary border border-primary fw-semibold px-3 py-2">
                            🗓️ {{ $program->description ?? 'Ongoing' }}
                        </span>
                    </div>
                    <div class="mb-2 text-muted">
                        Sponsored by: {{ $program->description ?? '-' }}
                        @if($program->location)
                            &nbsp;|&nbsp; Location: {{ $program->location }}
                        @endif
                    </div>
                    <div class="mb-2">
                        <div class="fw-semibold text-muted">Distribution Date</div>
                        <div class="fw-bold text-primary">
                            {{ \Carbon\Carbon::parse($program->start_date)->format('l, F d, Y') ?? '-' }}
                            —
                            {{ \Carbon\Carbon::parse($program->end_date)->format('l, F d, Y') ?? '-' }}
                        </div>
                    </div>
                    <hr>
                    <h5 class="fw-bold mb-3">Beneficiaries</h5>
                    <div class="row g-3">
                        @foreach($data['beneficiaries'] as $benData)
                            @php $ben = $benData['beneficiary']; @endphp
                            <div class="col-12 col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="fw-semibold mb-1">{{ $ben->first_name }} {{ $ben->last_name }}</div>
                                    <div class="mb-2">
                                        <div class="fw-semibold text-muted">Progress</div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: {{ $benData['progress'] }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ $benData['progress'] }}%</small>
                                    </div>
                                    <div class="mb-2">
                                        <a href="{{ route('brgyrep.track-applications.show', [$program->id, $ben->id]) }}"
                                           class="btn btn-outline-info w-100 mt-2">
                                            View Submitted Documents
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">No aid programs found for your barangay.</div>
    @endforelse
</div>
@endsection
