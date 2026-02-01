@extends('layouts.adminlayout')

@section('title', 'Schedules Record')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <a href="{{ route('distribution.category') }}{{ $encryptedBarangay ? '?barangay_id='.$encryptedBarangay : '' }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>

        <h4 class="mb-0 flex-grow-1 text-center">{{ ucfirst($status) }} Schedule Records</h4>

        <div class="d-flex align-items-center gap-2">

            {{-- Sort --}}
            <form method="GET" class="me-2">
                <input type="hidden" name="view" value="{{ $view }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="barangay_id" value="{{ request()->query('barangay_id') }}">
                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="recent" {{ $sort=='recent'?'selected':'' }}>Recently Created</option>
                    <option value="early"  {{ $sort=='early'?'selected':'' }}>Earliest Schedule</option>
                    <option value="last"   {{ $sort=='last'?'selected':'' }}>Latest Schedule</option>
                </select>
            </form>

            {{-- Search --}}
            <form method="GET" class="d-flex">
                <input type="hidden" name="view" value="{{ $view }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="barangay_id" value="{{ request()->query('barangay_id') }}">
                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search program.." value="{{ $search }}">
                <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
            </form>

            {{-- Toggle View --}}
            <form method="GET" class="ms-2">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="barangay_id" value="{{ request()->query('barangay_id') }}">
                <input type="hidden" name="view" value="{{ $view=='card' ? 'list' : 'card' }}">
                <button class="btn btn-outline-primary" title="Toggle List/Grid">
                    <i class="bi {{ $view=='card' ? 'bi-list-ul' : 'bi-grid-3x3-gap' }}"></i>
                </button>
            </form>

        </div>
    </div>

    {{-- Card View --}}
    <div class="{{ $view=='card' ? 'row g-3' : 'd-none' }}">
        @forelse($schedules as $s)

            @php
                $aid = optional($s->aidProgram);
                $image = $aid->background_image ? asset('storage/'.$aid->background_image) : asset('img/default-placeholder.jpg');
                $encSchedule = urlencode(encrypt($s->id));
                $encBrgy = $encryptedBarangay;
            @endphp

            <div class="col-md-6">
                <a href="{{ route('distribution.beneficiaries', [$encSchedule, $encBrgy]) }}" class="text-decoration-none">
                    <div class="card schedule-card">
                        <div class="row g-0 align-items-center">
                            <div class="col-auto">
                                <img src="{{ $image }}" style="width:120px;height:80px;object-fit:cover;" class="rounded-start">
                            </div>
                            <div class="col">
                                <div class="card-body py-2">
                                    <h6 class="mb-1">{{ $aid->aid_program_name }}</h6>
                                    <p class="text-muted small mb-0">
                                        Type: {{ ucfirst($s->beneficiary_type) }} |
                                        Start: {{ $s->start_date->format('Y-m-d') }} |
                                        End: {{ $s->end_date->format('Y-m-d') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        @empty
            <div class="text-center text-muted">No schedules found.</div>
        @endforelse
    </div>

    {{-- List View --}}
    <div class="list-group {{ $view=='list' ? '' : 'd-none' }}">
        @forelse($schedules as $s)

            @php
                $aid = optional($s->aidProgram);
                $image = $aid->background_image ? asset('storage/'.$aid->background_image) : asset('img/default-placeholder.jpg');
                $encSchedule = urlencode(encrypt($s->id));
                $encBrgy = $encryptedBarangay;
            @endphp

            <a class="list-group-item list-group-item-action d-flex align-items-center gap-3"
               href="{{ route('distribution.beneficiaries', [$encSchedule, $encBrgy]) }}">
                <img src="{{ $image }}" style="width:60px;height:60px;object-fit:cover;" class="rounded">
                <div>
                    <strong>{{ $aid->aid_program_name }}</strong><br>
                    <small>
                        Type: {{ ucfirst($s->beneficiary_type) }},
                        Start: {{ $s->start_date->format('Y-m-d') }},
                        End: {{ $s->end_date->format('Y-m-d') }}
                    </small>
                </div>
            </a>

        @empty
            <div class="list-group-item text-center text-muted">No schedules available.</div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
.schedule-card {
    transition: 0.2s;
}
.schedule-card:hover {
    transform: scale(1.03);
    box-shadow: 0 8px 32px rgba(13,110,253,0.15);
}
</style>
@endpush
