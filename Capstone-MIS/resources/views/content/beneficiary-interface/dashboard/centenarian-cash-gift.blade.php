{{-- filepath: c:\Lara\Capstone-MIS\resources\views\content\beneficiary-interface\dashboard\centenarian-cash-gift.blade.php --}}
@extends('layouts.beneficiarieslayout')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center mb-4">
        <div class="col-lg-8 col-md-10 col-12">
            <div class="alert alert-info d-flex align-items-center shadow-sm rounded-4 border-0 p-3 p-md-4" role="alert">
                <i class="bi bi-gift-fill fs-2 me-3 text-primary"></i>
                <div>
                    <h5 class="mb-1 fw-bold">Centenarian Cash Gift Status</h5>
                    <p class="mb-0">
                        You are eligible to receive a <strong>₱10,000 cash gift</strong> upon turning
                        <span class="badge bg-primary">80</span>,
                        <span class="badge bg-primary">85</span>,
                        <span class="badge bg-primary">90</span>,
                        <span class="badge bg-primary">95</span> years old.<br>
                        <strong>₱100,000 cash gift</strong> upon turning
                        <span class="badge bg-success">100</span> years old and every year after.<br>
                        <span class="text-muted">Check your milestone status below.</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10 col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-primary text-white rounded-top-4 fw-semibold fs-5">
                    Your Milestone Status
                </div>
                <div class="card-body p-2 p-md-3">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Milestone Age</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Received At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($milestone_status as $milestone)
                                    <tr>
                                        <td>
                                            <span class="badge {{ $milestone['age'] >= 100 ? 'bg-success' : 'bg-info text-dark' }} fs-6">{{ $milestone['age'] }}</span>
                                        </td>
                                        <td>
                                            {{ $milestone['date'] ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <span class="fw-bold text-{{ $milestone['age'] >= 100 ? 'success' : 'primary' }}">
                                                ₱{{ number_format($milestone['amount'], 0) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($milestone['given'])
                                                <span class="badge bg-success">Received</span>
                                            @elseif($milestone['is_today'])
                                                <span class="badge bg-warning text-dark">Eligible Today!</span>
                                            @elseif($milestone['is_past'])
                                                <span class="badge bg-danger">Missed</span>
                                            @else
                                                <span class="badge bg-secondary">Not Yet Eligible</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $milestone['given_at'] ? \Carbon\Carbon::parse($milestone['given_at'])->format('F d, Y') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-muted small">
                        <i class="bi bi-info-circle"></i> If you are eligible but have not received your cash gift, please contact your barangay or MSWD office.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
