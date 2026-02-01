<div class="card aid-card shadow-sm border-0 h-100
    {{ $app['status_type'] === 'primary' ? 'aid-card-primary' : 'aid-card-danger' }}"
    data-app-id="{{ htmlspecialchars($app['id'], ENT_QUOTES, 'UTF-8') }}"
    data-app-type="{{ htmlspecialchars($app['type'], ENT_QUOTES, 'UTF-8') }}">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-bold mb-0">{{ htmlspecialchars($app['type'], ENT_QUOTES, 'UTF-8') }}</h5>
            <span class="badge bg-light text-{{ $app['status_type'] }} border border-{{ $app['status_type'] }} fw-semibold px-3 py-2">
                {{ $app['status_type'] === 'primary' ? '🗓️' : '✔️' }} {{ htmlspecialchars($app['status'], ENT_QUOTES, 'UTF-8') }}
            </span>
        </div>
        {{-- ID badge removed --}}
        <p class="mb-2 text-muted">{{ htmlspecialchars($app['description'], ENT_QUOTES, 'UTF-8') }}</p>

        <div class="mb-3">
            <div class="fw-semibold text-muted mb-1 mt-2">Progress</div>
            <div class="progress">
                <div class="progress-bar bg-{{ $app['status_type'] }}" style="width: {{ (int)$app['progress'] }}%"></div>
            </div>
            <small class="text-muted">{{ (int)$app['progress'] }}%</small>
        </div>

        <div class="row g-2 mb-2">
            <div class="col">
                <div class="fw-semibold text-muted">Applied</div>
                <div class="text-dark">{{ htmlspecialchars($app['applied'], ENT_QUOTES, 'UTF-8') }}</div>
            </div>
            <div class="col">
                <div class="fw-semibold text-muted">Last Update</div>
                <div class="text-dark">{{ htmlspecialchars($app['updated'], ENT_QUOTES, 'UTF-8') }}</div>
            </div>
        </div>

        @if($app['distribution_date'])
        <div class="distribution-date-box">
            <div class="fw-semibold text-muted mb-1">Distribution Date</div>
            <div class="fw-bold text-primary">{{ htmlspecialchars($app['distribution_date'], ENT_QUOTES, 'UTF-8') }}</div>
        </div>
        @endif

        @php
            $progressRaw = $app['progress'] ?? 0;
            $progress = (float) str_replace('%', '', $progressRaw);
        @endphp

        @if($app['can_apply'])
            @if($progress >= 100)
                <button type="button" class="btn btn-success mt-2 w-100" disabled>
                    <i class="bi bi-check-circle me-1"></i> Aid Received
                </button>
            @else
                <a href="#"
                   class="btn btn-primary mt-2 w-100 show-requirements-btn"
                   data-requirements-json='@json($app["requirements"] ?? [])'
                   data-app="{{ htmlspecialchars($app['id'], ENT_QUOTES, 'UTF-8') }}">
                    <i class="bi bi-check-circle me-1"></i> Apply Now
                </a>
            @endif
        @else
            <div class="alert alert-info mt-2">
                Application opens on {{ htmlspecialchars($app['applied'], ENT_QUOTES, 'UTF-8') }}.
            </div>
        @endif
    </div>
</div>
