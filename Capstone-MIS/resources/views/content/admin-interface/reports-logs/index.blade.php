@extends('layouts.adminlayout')

@section('title', 'Reports & Activity Logs')

@section('content')
<div class="container py-4">

    {{-- Nav tabs to switch between Activity Logs and Programs Report --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            {{-- changed route name from admin.reports.index -> reports.index --}}
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

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">Activity Graph</h3>
        <div class="d-flex gap-2 align-items-center">
            <select id="daysSelect" class="form-select form-select-sm">
                <option value="7">Last 7 days</option>
                <option value="30" selected>Last 30 days</option>
                <option value="90">Last 90 days</option>
            </select>

            <a id="exportCsvBtn" class="btn btn-outline-secondary btn-sm" target="_blank">Export CSV</a>
            <a id="exportPdfBtn" class="btn btn-outline-secondary btn-sm" target="_blank">Export PDF</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card p-3">
                <canvas id="activityChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h6 class="mb-3">Top Actions</h6>
                <ul id="topActionsList" class="list-unstyled mb-0"></ul>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <strong>Recent Activity</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" id="activityTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Subject</th>
                            <th>Meta</th>
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('activityChart').getContext('2d');
    let chart = new Chart(ctx, {
        type: 'line',
        data: { labels: [], datasets: [{ label: 'Events', data: [], borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.08)', fill: true }] },
        options: { scales: { x: { display: true }, y: { beginAtZero: true } } }
    });

    const daysSelect = document.getElementById('daysSelect');
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const exportPdfBtn = document.getElementById('exportPdfBtn');

    function loadData(days) {
        fetch('{{ route("admin.reports.data") }}?days=' + days)
            .then(r => r.json())
            .then(json => {
                chart.data.labels = json.labels;
                chart.data.datasets[0].data = json.values;
                chart.update();

                // top actions
                const topActionsList = document.getElementById('topActionsList');
                topActionsList.innerHTML = '';
                json.topActions.forEach(a => {
                    const li = document.createElement('li');
                    li.className = 'mb-2';
                    li.innerHTML = `<strong>${a.action}</strong> <span class="text-muted">(${a.total})</span>`;
                    topActionsList.appendChild(li);
                });

                // recent table
                const tbody = document.querySelector('#activityTable tbody');
                tbody.innerHTML = '';
                json.recent.forEach(r => {
                    const user = r.user ? (r.user.full_name ?? (r.user.fname + ' ' + (r.user.lname ?? ''))) : 'System';
                    const meta = r.meta ? JSON.stringify(r.meta) : '';
                    // extract short class name without namespace and without id
                    const subject = r.subject_type ? (r.subject_type.replace(/^.*\\/, '')) : '-';
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${user}</td><td>${r.action}</td><td>${subject}</td><td><small class="text-muted">${meta}</small></td><td>${new Date(r.created_at).toLocaleString()}</td>`;
                    tbody.appendChild(tr);
                });
            })
            .catch(console.error);
    }

    function updateExportLinks() {
        const days = daysSelect.value;
        exportCsvBtn.href = '{{ route("admin.reports.export.csv") }}?days=' + days;
        exportPdfBtn.href = '{{ route("admin.reports.export.pdf") }}?days=' + days;
    }

    daysSelect.addEventListener('change', function () {
        updateExportLinks();
        loadData(this.value);
    });

    updateExportLinks();
    loadData(daysSelect.value);
});
</script>
@endpush
