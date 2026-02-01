@extends('layouts.adminlayout')

<?php
    use App\Models\BeneficiaryDocument;
    use App\Models\MSWDMember;
?>

@section('content')
<div class="container py-4">
    {{-- Display SMS debug/info message if present --}}
    @if(session('sms_debug'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>SMS Info:</strong> {{ session('sms_debug') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $encryptedBarangay = $encryptedBarangay ?? (isset($barangay) ? urlencode(encrypt($barangay->id)) : null);
        $encryptedSchedule = $encryptedSchedule ?? (isset($schedule) ? urlencode(encrypt($schedule->id)) : null);
    @endphp

    <div class="mb-3 d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary" onclick="history.back(); return false;">
            <i class="bi bi-arrow-left"></i> Back
        </button>
        @if($encryptedBarangay && $encryptedSchedule)
            <a href="{{ route('distribution.beneficiaries', [$encryptedSchedule, $encryptedBarangay]) }}" class="btn btn-outline-secondary d-none" id="backFallbackSchedule">Back (fallback)</a>
        @elseif($encryptedBarangay)
            <a href="{{ route('distribution.category') }}?barangay_id={{ $encryptedBarangay }}" class="btn btn-outline-secondary d-none" id="backFallbackCategory">Back (fallback)</a>
        @endif
    </div>

    <h4 class="mb-3">Distribution Record: Beneficiaries for {{ $barangay->barangay_name }}</h4>

    <a href="{{ route('distribution.beneficiaries.export', [encrypt($schedule->id), encrypt($barangay->id)]) }}" class="btn btn-success mb-3">
        <i class="fa fa-download"></i> Export Beneficiaries CSV
    </a>

    @php
        use App\Models\AidReceipt;
    @endphp

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Eligibility</th>
                    <th>Progress</th>
                    <th>Confirm Distribution</th>
                    <th>Beneficiary Receipt</th> {{-- NEW COLUMN --}}
                </tr>
            </thead>
            <tbody>
                @forelse($beneficiaries as $index => $b)
                    @php
                        $alreadyReceived = DB::table('program_beneficiary_receipts')
                            ->where('beneficiary_id', $b->id)
                            ->where('schedule_id', $schedule->id)
                            ->exists();

                        // Check aid_receipts table
                        $aidReceipt = \App\Models\AidReceipt::where('beneficiary_id', $b->id)
                            ->where('schedule_id', $schedule->id)
                            ->first();

                        // If not found in aid_receipts but found in program_beneficiary_receipts, treat as confirmed
                        if (!$aidReceipt && $alreadyReceived) {
                            $receiptDate = DB::table('program_beneficiary_receipts')
                                ->where('beneficiary_id', $b->id)
                                ->where('schedule_id', $schedule->id)
                                ->value('received_at');
                            $aidReceipt = (object)[
                                'receipt_date' => $receiptDate,
                            ];
                        }

                        // Progress calculation
                        $requirements = isset($b->requirements_status) && is_array($b->requirements_status) ? $b->requirements_status : [];
                        $total = count($requirements);
                        $submitted = 0;
                        $reviewed = 0;
                        $verified = 0;
                        foreach($requirements as $req) {
                            if (!empty($req['status']) && $req['status'] !== 'Not Submitted') $submitted++;
                            if (!empty($req['status']) && in_array($req['status'], ['Reviewed', 'Validated', 'Rejected', 'Pending Review'])) $reviewed++;
                            if (!empty($req['status']) && $req['status'] === 'Validated') $verified++;
                        }
                        // Eligibility logic
                        $eligibilityLabel = '';
                        if ($b->is_eligible) {
                            $eligibilityLabel = '<span class="badge bg-primary">Eligible to Receive</span>';
                        } elseif ($total > 0 && $submitted === $total && $verified < $total) {
                            $eligibilityLabel = '<span class="badge bg-info text-dark">Need Verification</span>';
                        } else {
                            $eligibilityLabel = '<span class="badge bg-warning text-dark">Incomplete Requirements</span>';
                        }
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $b->last_name }}, {{ $b->first_name }}</td>
                        <td>{{ $b->beneficiary_type }}</td>
                        <td><span class="badge bg-success">Verified</span></td>
                        <td id="eligibility-{{ $b->id }}">{!! $eligibilityLabel !!}</td>
                        <td>
                            @if($total > 0)
                                <div class="mb-1 small">
                                    Submitted: {{ $submitted }}/{{ $total }} |
                                    Reviewed: {{ $reviewed }}/{{ $total }} |
                                    Verified: {{ $verified }}/{{ $total }}
                                </div>
                                <div class="position-relative" style="height: 28px;">
                                    @if($submitted > 0)
                                        @php
                                            if ($submitted < $total) {
                                                $progressLevel = 33;
                                                $progressColor = 'bg-primary';
                                            } elseif ($reviewed < $total) {
                                                $progressLevel = 66;
                                                $progressColor = 'bg-warning';
                                            } elseif ($verified < $total) {
                                                $progressLevel = 99;
                                                $progressColor = 'bg-success';
                                            } else {
                                                $progressLevel = 100;
                                                $progressColor = 'bg-success';
                                            }
                                        @endphp
                                        <div class="progress" style="height: 12px;">
                                            <div class="progress-bar {{ $progressColor }}"
                                                 role="progressbar"
                                                 style="width: {{ $progressLevel }}%"
                                                 aria-valuenow="{{ $progressLevel }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    @endif
                                    {{-- Show number or check in each checkpoint --}}
                                    @foreach([0, 1, 2] as $i)
                                        @php
                                            $checkpointColor = $i == 0 ? 'bg-primary' : ($i == 1 ? 'bg-warning' : 'bg-success');
                                            $isChecked = ($i == 0 && $submitted == $total) ||
                                                         ($i == 1 && $reviewed == $total) ||
                                                         ($i == 2 && $verified == $total);
                                        @endphp
                                        <span style="
                                            position: absolute;
                                            top: -4px;
                                            left: calc({{ $i / 2 * 100 }}% - 12px);
                                            z-index: 2;
                                        ">
                                            <span class="rounded-circle {{ $checkpointColor }} border border-2 border-white d-flex align-items-center justify-content-center"
                                                  style="width: 20px; height: 20px; font-size: 1rem; color: #fff;">
                                                @if($isChecked)
                                                    <i class="bi bi-check-lg"></i>
                                                @else
                                                    {{ $i+1 }}
                                                @endif
                                            </span>
                                        </span>
                                    @endforeach
                                </div>
                                <div class="mt-1">
                                    <a href="#" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewDocumentsModal-{{ $b->id }}">
                                        Details
                                    </a>
                                </div>
                                <!-- Modal -->
                                <div class="modal fade"
                                     id="viewDocumentsModal-{{ $b->id }}"
                                     tabindex="-1"
                                     aria-labelledby="viewDocumentsLabel-{{ $b->id }}"
                                     aria-hidden="true"
                                     data-bs-backdrop="static"
                                     data-bs-keyboard="false">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="viewDocumentsLabel-{{ $b->id }}">Document Progress for {{ $b->first_name }} {{ $b->last_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                @foreach($b->requirements_status as $req)
                                                    @php
                                                        $docId = $req['document_id'] ?? null;
                                                        $docModel = $docId ? BeneficiaryDocument::find($docId) : null;
                                                        $repName = null;
                                                        $reviewedBy = null;
                                                        $verifiedBy = null;
                                                        if ($docModel && $docModel->assisted_by) {
                                                            $rep = MSWDMember::find($docModel->assisted_by);
                                                            if ($rep) {
                                                                $repName = $rep->full_name ?: trim(($rep->fname ?? '') . ' ' . ($rep->lname ?? ''));
                                                            }
                                                        }
                                                        if ($docModel && $docModel->reviewed_by) {
                                                            $reviewer = MSWDMember::find($docModel->reviewed_by);
                                                            if ($reviewer) {
                                                                $reviewedBy = $reviewer->full_name ?: trim(($reviewer->fname ?? '') . ' ' . ($reviewer->lname ?? ''));
                                                            }
                                                        }
                                                        if ($docModel && $docModel->verified_by) {
                                                            $verifier = MSWDMember::find($docModel->verified_by);
                                                            if ($verifier) {
                                                                $verifiedBy = $verifier->full_name ?: trim(($verifier->fname ?? '') . ' ' . ($verifier->lname ?? ''));
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="mb-3 p-2 border rounded">
                                                        <strong>{{ $req['name'] ?? 'Requirement' }}</strong>
                                                        <span class="badge
                                                            @if($req['status'] === 'Validated') bg-success
                                                            @elseif($req['status'] === 'Rejected') bg-danger
                                                            @elseif($req['status'] === 'Pending' || $req['status'] === 'Pending Review') bg-warning text-dark
                                                            @else bg-secondary
                                                            @endif">{{ $req['status'] }}</span>
                                                        <div class="mt-2">
                                                            @if($docModel)
                                                                <a href="{{ asset('storage/' . $docModel->file_path) }}" target="_blank" class="btn btn-outline-info btn-sm">View Document</a>
                                                            @else
                                                                <span class="badge bg-secondary">No Document</span>
                                                            @endif
                                                        </div>
                                                        <div class="mt-2">
                                                            @if($docModel && $docModel->assisted_by)
                                                                <span class="text-info small">
                                                                    <i class="bi bi-person-badge"></i>
                                                                    Submitted by Barangay Rep:
                                                                    {{ $repName ? $repName : 'ID: ' . $docModel->assisted_by }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="mt-2">
                                                            @if($docModel && $docModel->reviewed_by)
                                                                <span class="text-primary small">
                                                                    <i class="bi bi-person-check"></i>
                                                                    Reviewed by Brgy Rep:
                                                                    {{ $reviewedBy ? $reviewedBy : 'ID: ' . $docModel->reviewed_by }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="mt-2">
                                                            @if($docModel && $docModel->verified_by)
                                                                <span class="text-success small">
                                                                    <i class="bi bi-person-check-fill"></i>
                                                                    Verified by MSWD:
                                                                    {{ $verifiedBy ? $verifiedBy : 'ID: ' . $docModel->verified_by }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td id="received-{{ $b->id }}">
                            @if($b->is_eligible && !$alreadyReceived)
                                <form method="POST" action="{{ route('distribution.markReceived') }}" class="mark-received-form">
                                    @csrf
                                    <input type="hidden" name="beneficiary_id" value="{{ $b->id }}">
                                    <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                    <button type="submit" class="btn btn-success btn-sm">Confirm Distribution</button>
                                </form>
                            @elseif($alreadyReceived)
                                <span class="badge bg-info">Distribution Confirmed</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($aidReceipt && $aidReceipt->receipt_date)
                                <span class="badge bg-success">
                                    Confirmed by Beneficiary<br>
                                    <small>{{ \Carbon\Carbon::parse($aidReceipt->receipt_date)->format('m/d/Y h:i A') }}</small>
                                </span>
                            @else
                                <span class="badge bg-secondary">Not Yet Confirmed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No verified beneficiaries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
