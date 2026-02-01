@extends('layouts.brgylayout')

@section('title', 'View Schedules')

@section('content')
<style>
@media (max-width: 768px) {
    .table-responsive {
        border-radius: 10px;
        border: 1px solid #dee2e6;
        background: #fff;
        margin-bottom: 1rem;
        padding-bottom: 5px;
    }
    table.table {
        font-size: 13px;
    }
    table.table thead {
        display: none;
    }
    table.table tbody tr {
        display: block;
        margin-bottom: 12px;
        border-bottom: 1px solid #eee;
        background: #f8f9fa;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    table.table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: none;
        width: 100%;
        padding: 8px 8px !important;
        white-space: normal;
    }
    table.table td:before {
        content: attr(data-label);
        font-weight: 600;
        color: #495057;
        flex-basis: 50%;
        text-align: left;
        padding-right: 10px;
    }
    .badge {
        font-size: 12px;
        padding: 6px 8px;
    }
}
</style>
<div class="container py-4">
    <h2 class="mb-4 fw-bold fs-5 fs-md-3">Aid Program Schedules</h2>
    <div class="table-responsive">
        <table class="table table-bordered align-middle bg-white mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Program Name</th>
                    <th>Program Type</th>
                    <th>Beneficiary Type</th>
                    <th>Barangays</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $i => $schedule)
                    @php
                        $now = now();
                        if ($now->lt($schedule->start_date)) $status = 'Upcoming';
                        elseif ($now->between($schedule->start_date, $schedule->end_date)) $status = 'Ongoing';
                        else $status = 'Completed';
                    @endphp
                    <tr>
                        <td data-label="#">{{ $i + 1 }}</td>
                        <td data-label="Program Name">{{ $schedule->aidProgram->aid_program_name ?? '-' }}</td>
                        <td data-label="Program Type">{{ $schedule->aidProgram->programType->program_type_name ?? '-' }}</td>
                        <td data-label="Beneficiary Type">
                            @if($schedule->beneficiary_type === 'senior')
                                Senior Citizen
                            @elseif($schedule->beneficiary_type === 'pwd')
                                PWD
                            @else
                                Both
                            @endif
                        </td>
                        <td data-label="Barangays">
                            @if(is_array($schedule->barangay_ids) && count($schedule->barangay_ids))
                                <ul class="mb-0 ps-3">
                                    @foreach($schedule->barangay_ids as $bid)
                                        <li>{{ $barangays[$bid] ?? 'Unknown' }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">All</span>
                            @endif
                        </td>
                        <td data-label="Start Date">{{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') }}</td>
                        <td data-label="End Date">{{ \Carbon\Carbon::parse($schedule->end_date)->format('M d, Y') }}</td>
                        <td data-label="Status">
                            <span class="badge
                                @if($status === 'Upcoming') bg-info
                                @elseif($status === 'Ongoing') bg-success
                                @else bg-secondary
                                @endif
                            ">
                                {{ $status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No schedules found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
