@extends('layouts.adminlayout')

@section('title', 'MSWD Dashboard')

@section('content')
    <!-- Quick stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <small class="text-muted">Total Beneficiaries</small>
                <div class="h4 fw-bold mt-1">Blank</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <small class="text-muted">Scheduled Distributions</small>
                <div class="h4 fw-bold mt-1">Blank</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <small class="text-muted">Pending Verifications</small>
                <div class="h4 fw-bold mt-1">Blank</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <small class="text-muted">Unread Notifications</small>
                <div class="h4 fw-bold mt-1">Blank</div>
            </div>
        </div>
    </div>

    <!-- Recent beneficiaries table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <div>
                <h5 class="mb-0">Recent Beneficiaries</h5>
                <small class="text-muted">Latest registered PWDs and Senior Citizens</small>
            </div>
            <div>
                <a href="#" class="btn btn-sm btn-primary">New Beneficiary</a>
                <a href="#" class="btn btn-sm btn-outline-secondary">Export CSV</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Name</th><th>Type</th><th>Barangay</th>
                        <th>Contact</th><th>Status</th><th>Last Aid</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent as $r)
                    <tr>
                        <td>Blank</td>
                        <td>Blank</td>
                        <td>Blank</td>
                        <td>Blank</td>
                        <td>Blank</td>
                        <td><span class="badge bg-warning text-dark">Blank</span></td>
                        <td>Blank</td>
                        <td class="text-end">
                            <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                            <a href="#" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <small class="text-muted">Showing recent entries</small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item active"><a class="page-link">1</a></li>
                </ul>
            </nav>
        </div>
    </div>

@endsection
