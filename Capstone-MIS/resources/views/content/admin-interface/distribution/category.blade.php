@extends('layouts.adminlayout')

@section('content')
<div class="container py-4">
    @if(isset($barangay) && $barangay)
        <div class="alert alert-light border-start border-4 border-primary d-flex justify-content-between align-items-center">
            <div>
                <strong>Selected Barangay:</strong> {{ $barangay->barangay_name }}
            </div>
            <div>
                <a href="{{ route('distribution.barangays') }}" class="btn btn-sm btn-outline-secondary">Change</a>
            </div>
        </div>
    @endif

    <h2 class="mb-4 text-center fw-bold">Program Distribution</h2>
    <div class="row g-4 justify-content-center">
        @php
            $encryptedBarangay = isset($barangay) ? urlencode(encrypt($barangay->id)) : null;
        @endphp

        @foreach([
            ['Upcoming', asset('img/upcoming-sched.jpg'), 'info', 'card-delay-1'],
            ['Ongoing', asset('img/ongoing-sched.jpg'), 'success', 'card-delay-2'],
            ['Completed', asset('img/completed-sched.jpg'), 'secondary', 'card-delay-3']
        ] as [$status, $img, $color, $delay])
            <div class="col-md-4">
                {{-- include encrypted barangay_id as query param when available --}}
                <a href="{{ route('distribution.schedules', $status) }}{{ $encryptedBarangay ? '?barangay_id='.$encryptedBarangay : '' }}" class="text-decoration-none">
                     <div class="card shadow-lg border-0 h-100 text-center status-card hover-card {{ $delay }} bg-{{ $color }} position-relative"
                         style="cursor:pointer;">
                         <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                             <div class="rounded-circle bg-white mb-3 d-flex align-items-center justify-content-center"
                                  style="width:160px; height:160px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,0.10);">
                                 <img src="{{ $img }}" alt="{{ $status }} Schedule" style="width:160px; height:160px; object-fit:cover; border-radius:50%;">
                             </div>
                             <h4 class="card-title fw-bold text-{{ $color == 'secondary' ? 'dark' : 'white' }} mb-2">{{ $status }}</h4>
                             <p class="mb-0 {{ $color == 'secondary' ? 'text-dark' : 'text-white' }}">View schedules</p>
                         </div>
                         <div class="position-absolute top-0 end-0 m-2">
                             <span class="badge bg-white text-{{ $color }} shadow-sm fs-6">Go</span>
                         </div>
                     </div>
                 </a>
             </div>
         @endforeach
     </div>
 </div>
 @endsection

@push('styles')
<style>
.status-card {
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    opacity: 0;
    transform: translateY(20px) scale(0.95);
    animation: fadeIn 0.5s forwards;
}

@keyframes fadeIn {
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.status-card:hover {
    transform: scale(1.08);
    box-shadow: 0 20px 48px rgba(0,0,0,0.18), 0 2px 8px rgba(0,0,0,0.10);
    filter: brightness(0.97);
    z-index: 2;
}
.status-card .card-title {
    letter-spacing: 1px;
}
</style>
@endpush
