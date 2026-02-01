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

        @if(!empty($app['distribution_start']) || !empty($app['distribution_end']) || !empty($app['distribution_date']))
        <div class="distribution-date-box">
            <div class="fw-semibold text-muted mb-1">Distribution Date</div>
            <div class="fw-bold text-primary">
                @if(!empty($app['distribution_start']) && !empty($app['distribution_end']) && $app['distribution_start'] !== $app['distribution_end'])
                    {{ $app['distribution_start'] }} — {{ $app['distribution_end'] }}
                @else
                    {{ $app['distribution_date'] ?? $app['distribution_start'] ?? $app['distribution_end'] }}
                @endif
            </div>
        </div>
        @endif

        {{-- Button logic: show exactly one button based on progress and application state --}}
        @php
            $progressRaw = $app['progress'] ?? 0;
            $progress = (float) str_replace('%', '', $progressRaw);

            // determine if all requirements are applied (if requirement_ids and requirements_status provided)
            $allApplied = true;
            if (!empty($app['requirement_ids'])) {
                foreach ($app['requirement_ids'] as $reqId) {
                    if (!isset($app['requirements_status'][$reqId]) || $app['requirements_status'][$reqId] !== 'Applied') {
                        $allApplied = false;
                        break;
                    }
                }
            }

            $requirementsJson = json_encode($app['requirements'] ?? []);

            // check if beneficiary already confirmed receipt (shows "Confirmed Received" instead of clickable button)
            $beneficiaryId = \Illuminate\Support\Facades\Auth::guard('beneficiary')->id();
            $aidProgramId = $app['program_id'] ?? ($app['id'] ?? null);
            $hasReceipt = false;
            if ($beneficiaryId && $aidProgramId) {
                $hasReceipt = \App\Models\AidReceipt::where('beneficiary_id', $beneficiaryId)
                    ->where('aid_program_id', $aidProgramId)
                    ->exists();
            }

            $modalId = 'aidReceivedModal'.htmlspecialchars($app['id'], ENT_QUOTES, 'UTF-8');
        @endphp

        @if($hasReceipt)
            <!-- Already confirmed: show non-clickable confirmed state -->
            <div class="d-grid mt-2">
                <button type="button" class="btn btn-success w-100" disabled aria-disabled="true">
                    <i class="bi bi-check-circle me-1"></i> Confirmed Received
                </button>
            </div>
        @elseif($progress >= 100)
            <!-- Confirmation modal trigger (same as before) -->
            <!-- Styles for the confirmation modal (move to global CSS if preferred) -->
            <style>
                /* modal card */
                .aid-confirm-modal .modal-content {
                    border-radius: 14px;
                    padding: 1.25rem;
                    box-shadow: 0 10px 30px rgba(16,24,40,0.08);
                    border: none;
                }
                .aid-confirm-modal .modal-header { border: 0; display: flex; justify-content: flex-end; padding: 0; }
                .aid-confirm-modal .modal-body { text-align: center; padding: 0.25rem 1.25rem 1rem; }
                .aid-confirm-modal .modal-body .icon { width:56px;height:56px;border-radius:12px;background:#ecfdf5;display:inline-flex;align-items:center;justify-content:center;color:#16a34a;margin-bottom:.75rem;font-size:1.25rem; }
                .aid-confirm-modal .modal-title { font-weight:700;font-size:1.25rem;margin-bottom:.25rem; }
                .aid-confirm-modal .modal-sub { color:#6b7280;margin-bottom:1rem;font-size:.95rem; }
                .aid-confirm-modal .btn-confirm { background:#16a34a;border:none;color:#fff;border-radius:10px;padding:.65rem 1rem; }
                .aid-confirm-modal .btn-cancel { background:transparent;border-radius:10px;padding:.6rem 1rem;border:1px solid #e6e9ef;color:#374151; }
            </style>

            <div class="modal fade aid-confirm-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>

                        <div class="modal-body">
                            <div class="icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                    <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zM6.5 11.5L3 8l1.4-1.4L6.5 8.7 11.6 3.6 13 5 6.5 11.5z"/>
                                </svg>
                            </div>

                            <h5 class="modal-title" id="{{ $modalId }}Label">Confirm Aid Received</h5>
                            <p class="modal-sub">Are you sure you received the aid for <strong>{{ htmlspecialchars($app['type'], ENT_QUOTES, 'UTF-8') }}</strong>? This action will record the receipt for your application.</p>

                            <div class="text-start mb-3" style="font-size:.95rem;color:#4b5563;">
                                <div><strong>Distribution Date:</strong> {{ $app['distribution_date'] ?? 'TBD' }}</div>
                            </div>

                            <form method="POST" action="{{ route('beneficiaries.application.confirm_received', $aidProgramId) }}">
                                @csrf
                                <input type="hidden" name="aid_program_id" value="{{ $aidProgramId }}">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-confirm">Confirm Receipt</button>
                                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid mt-2">
                <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                    <i class="bi bi-check-circle me-1"></i> Aid Received
                </button>
            </div>
        @else
            @if($allApplied)
                <button type="button" class="btn btn-success mt-2 w-100" disabled>
                    <i class="bi bi-check-circle me-1"></i> Applied
                </button>
            @else
                {{-- Apply Now redirects to submit document for first requirement --}}
                @php
                    $firstRequirementId = $app['requirement_ids'][0] ?? null;
                @endphp
                @if($firstRequirementId)
                    <a href="{{ route('beneficiary.submit-document.form', [$app['program_id'], $firstRequirementId]) }}"
                        class="btn btn-primary mt-2 w-100">
                        <i class="bi bi-check-circle me-1"></i> Apply Now
                    </a>
                @else
                    <button type="button" class="btn btn-primary mt-2 w-100" disabled>
                        <i class="bi bi-check-circle me-1"></i> Apply Now
                    </button>
                @endif
            @endif
        @endif

    </div>
</div>
