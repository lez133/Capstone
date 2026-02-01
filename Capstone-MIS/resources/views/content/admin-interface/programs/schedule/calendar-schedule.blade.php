@extends('layouts.adminlayout')

@section('title', 'Schedule Calendar')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<link href="{{ asset('css/calendar.css') }}" rel="stylesheet">

<div class="container mt-4 mb-4">

    {{-- ✅ Back Button --}}
    <div class="mb-3">
        <a href="{{ route('schedule.index') }}" class="btn-back">
            ← Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">📅 Schedule Calendar</h5>
        </div>
        <div class="card-body">
            <div id="scheduleCalendar"></div>
        </div>
    </div>
</div>

{{-- ✅ Schedule Modal --}}
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="scheduleModalLabel">Schedule Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Program:</strong> <span id="modalProgram"></span></li>
                    <li class="list-group-item"><strong>Beneficiary Type:</strong> <span id="modalBeneficiary"></span></li>
                    <li class="list-group-item"><strong>Date Range:</strong> <span id="modalTime"></span></li>
                    <li class="list-group-item"><strong>Status:</strong> <span id="modalStatus"></span></li>
                    <li class="list-group-item"><strong>Barangays:</strong> <span id="modalBarangays"></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>


<script>
    window.schedules = @json($schedules);
</script>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/calendar.js') }}"></script>
@endsection
