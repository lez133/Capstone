@extends('layouts.adminlayout')

@section('title', 'Manage Schedules')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Manage Schedules</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('schedules.create') }}" class="btn btn-primary mb-3">Create Schedule</a>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Aid Program</th>
                        <th>Barangays</th>
                        <th>Beneficiary Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $index => $schedule)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $schedule->aidProgram->aid_program_name }}</td>
                            <td>
                                @php
                                    $barangayNames = \App\Models\Barangay::whereIn('id', $schedule->barangay_ids)->pluck('barangay_name')->toArray();
                                @endphp
                                {{ implode(', ', $barangayNames) }}
                            </td>
                            <td>{{ ucfirst($schedule->beneficiary_type) }}</td>
                            <td>{{ $schedule->start_date }}</td>
                            <td>{{ $schedule->end_date }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No schedules found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
