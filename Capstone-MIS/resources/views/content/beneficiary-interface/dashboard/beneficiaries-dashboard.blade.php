@extends('layouts.beneficiarieslayout')

@section('content')
<link rel="stylesheet" href="{{ asset('css/beneficiarydashboard.css') }}">

<div class="container-fluid px-3 py-3">
    {{-- ===== TOP ROW ===== --}}
    <div class="row g-3 align-items-stretch mb-3">
        {{-- Personal Details --}}
        <div class="col-lg-4 col-md-6">
            <div class="card dashboard-card h-100 shadow-sm rounded-4 border-0">
                <div class="card-header bg-light fw-semibold fs-5 rounded-top-4">
                    Personal Details
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> John Doe</p>
                    <p><strong>Email:</strong> john@example.com</p>
                    <p><strong>Phone:</strong> +123 456 789</p>
                </div>
            </div>
        </div>

        {{-- Document Status --}}
        <div class="col-lg-4 col-md-6">
            <div class="card dashboard-card h-100 shadow-sm rounded-4 border-0">
                <div class="card-header bg-light fw-semibold fs-5 rounded-top-4">
                    Document Status
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between align-items-center mb-2">
                            ID <span class="badge bg-success">Verified</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center mb-2">
                            Passport <span class="badge bg-warning text-dark">Pending</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            Address Proof <span class="badge bg-success">Verified</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- SMS Alerts --}}
        <div class="col-lg-4 col-md-12">
            <div class="card dashboard-card h-100 shadow-sm rounded-4 border-0">
                <div class="card-header bg-light fw-semibold fs-5 rounded-top-4">
                    SMS Alerts
                </div>
                <div class="card-body">
                    <p class="mb-3">Receive SMS notifications for important updates.</p>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" checked>
                        <label class="form-check-label">Enabled</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SECOND ROW ===== --}}
    <div class="row g-3">
        {{-- Aid Application History --}}
        <div class="col-lg-8 col-md-12">
            <div class="card dashboard-card h-100 shadow-sm rounded-4 border-0">
                <div class="card-header bg-light fw-semibold fs-5 rounded-top-4">
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
                                <tr>
                                    <td>1</td>
                                    <td>Housing Aid</td>
                                    <td><span class="badge bg-success">Approved</span></td>
                                    <td>2025-02-12</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Education Grant</td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td>2025-03-21</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Medical Support</td>
                                    <td><span class="badge bg-danger">Rejected</span></td>
                                    <td>2025-01-08</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-lg-4 col-md-12">
            <div class="card dashboard-card h-100 shadow-sm rounded-4 border-0">
                <div class="card-header bg-light fw-semibold fs-5 rounded-top-4">
                    Quick Actions
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <button class="btn btn-primary w-100 rounded-pill">Apply for Aid</button>
                    <button class="btn btn-outline-secondary w-100 rounded-pill">Download Report</button>
                    <button class="btn btn-danger w-100 rounded-pill">Contact Support</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
