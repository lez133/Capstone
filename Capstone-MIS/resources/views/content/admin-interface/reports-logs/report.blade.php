@extends('layouts.adminlayout')

@section('title', 'Programs Coverage Report')

@section('content')
<div class="container py-4">

    {{-- Nav tabs --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}"
               href="{{ route('reports.index') }}">
               Activity Logs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.reports.overview') ? 'active' : '' }}"
               href="{{ route('admin.reports.overview') }}">
               Programs Report
            </a>
        </li>
    </ul>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Programs Coverage Report</h4>
            <small class="text-muted">
                Overview of programs, barangays and estimated beneficiaries.<br>
                <span class="text-primary">Enabled reporting features that generate data summaries and visualizations to help staff monitor progress, assess service delivery, and support evidence-based decision-making.</span>
            </small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <select id="periodSelect" class="form-select form-select-sm">
                <option value="7">Last 7 days</option>
                <option value="30" selected>Last 30 days</option>
                <option value="90">Last 90 days</option>
            </select>
            <a id="exportCsv" class="btn btn-outline-secondary btn-sm" target="_blank">Export CSV</a>
        </div>
    </div>

    <div class="row g-3 mb-4" id="summaryCards">
        <!-- populated by JS -->
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Programs Breakdown</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" id="programsTable">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Barangays</th>
                            <th>Estimated Beneficiaries</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Program Attendance Chart</strong></div>
        <div class="card-body">
            <canvas id="programChart" height="120"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const periodSelect = document.getElementById('periodSelect');
    const exportCsv = document.getElementById('exportCsv');
    const ctx = document.getElementById('programChart').getContext('2d');
    const chart = new Chart(ctx, { type: 'bar', data: { labels: [], datasets: [{ label: 'Estimated Beneficiaries', data: [], backgroundColor: '#0d6efd' }] }, options: { responsive: true } });

    function loadReport(days) {
        fetch('{{ route("admin.reports.coverage.data") }}?days=' + days)
            .then(r => r.json())
            .then(json => {
                // summary cards
                const container = document.getElementById('summaryCards');
                container.innerHTML = `
                    <div class="col-md-4">
                        <div class="card p-3">
                            <h6>Programs in period</h6>
                            <h3>${json.summary.program_count}</h3>
                            <small class="text-muted">${json.period.start} — ${json.period.end}</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3">
                            <h6>Barangays covered</h6>
                            <h3>${json.summary.barangay_count}</h3>
                            <small class="text-muted">Distinct barangays</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3">
                            <h6>Estimated beneficiaries</h6>
                            <h3>${json.summary.estimated_beneficiaries}</h3>
                            <small class="text-muted">Verified / eligible estimate</small>
                        </div>
                    </div>
                `;

                // table
                const tbody = document.querySelector('#programsTable tbody');
                tbody.innerHTML = '';
                json.per_program.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${p.name}</td><td>${p.barangay_count}</td><td>${p.estimated_beneficiaries}</td>`;
                    tbody.appendChild(tr);
                });

                // chart
                chart.data.labels = json.labels;
                chart.data.datasets[0].data = json.values;
                chart.update();

                // export link
                exportCsv.href = '{{ route("admin.reports.export.csv") ?? "#" }}?days=' + days;
            })
            .catch(console.error);
    }

    periodSelect.addEventListener('change', () => loadReport(periodSelect.value));
    loadReport(periodSelect.value);
});
</script>
@endpush
