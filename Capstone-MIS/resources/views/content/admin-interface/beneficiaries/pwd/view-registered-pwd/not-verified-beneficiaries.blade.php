@extends('layouts.adminlayout')

@section('title', 'Not Verified PWD Beneficiaries')

@section('content')
<div class="container py-4">

    {{-- flash messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('sms_debug'))
        <div class="alert alert-info">
            <strong>Notification status:</strong>
            <pre class="mb-0 small">{{ session('sms_debug') }}</pre>
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('pwd.manage', ['encryptedBarangayId' => $encryptedBarangayId]) }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Manage Beneficiaries
        </a>
    </div>

    <h4 class="mb-3">Not Verified PWD Beneficiaries - {{ $barangay->barangay_name }}</h4>

    {{-- Search form --}}
    <form method="GET" action="{{ route('pwd.not-verified', $encryptedBarangayId) }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by name or PWD ID" value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">
                <i class="fa fa-search"></i> Search
            </button>
        </div>
    </form>

    <!-- Search & Match Modal Trigger Button -->
    <button type="button" class="btn btn-info btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#searchPWDModal">
        <i class="fa fa-search"></i> Search Related PWD
    </button>

    <!-- Search & Match Modal -->
    <div class="modal fade" id="searchPWDModal" tabindex="-1" aria-labelledby="searchPWDModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
          <div class="modal-header">
            <h5 class="modal-title" id="searchPWDModalLabel">
                <i class="fa fa-search"></i> Find Related PWD Beneficiary
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="pwdSearchForm" autocomplete="off">
              <div class="mb-3">
                <label for="searchPWDInput" class="form-label">Search by Name or PWD ID</label>
                <input type="text" class="form-control" id="searchPWDInput" name="search" placeholder="Enter name or PWD ID">
              </div>
              <button type="submit" class="btn btn-primary w-100">
                <i class="fa fa-search"></i> Search
              </button>
            </form>
            <div id="searchPWDResults" class="mt-3"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Civil Status</th>
                    <th>PWD ID Number</th>
                    <th>Assisted By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notVerifiedBeneficiaries as $index => $b)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $b->last_name }}, {{ $b->first_name }} {{ $b->suffix }}</td>
                        <td>{{ $b->email ?: 'N/A' }}</td>
                        <td>{{ $b->phone ?: 'N/A' }}</td>
                        <td>{{ $b->age ?: 'N/A' }}</td>
                        <td>
                            @php $g = strtoupper(trim($b->gender ?? '')); @endphp
                            @if($g === 'M') Male
                            @elseif($g === 'F') Female
                            @else {{ $b->gender }} @endif
                        </td>
                        <td>{{ $b->civil_status ?: 'N/A' }}</td>
                        <td>{{ $b->pwd_id ?: 'N/A' }}</td>
                        <td>
                            @if($b->assisted_by)
                                @php
                                    $rep = \App\Models\MswdMember::find($b->assisted_by);
                                @endphp
                                {{ $rep ? $rep->fname . ' ' . $rep->lname : 'N/A' }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-warning text-dark">Not Verified</span>
                        </td>
                        <td>
                            {{-- Verify icon-only (opens modal, success theme) --}}
                            <button class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $b->id }}" title="Verify" aria-label="Verify" style="box-shadow:none;color:inherit;">
                                <i class="fa fa-check-circle text-success" style="font-size:1.1rem;"></i>
                            </button>

                            <!-- Delete (icon-only, red) - opens modal -->
                            <button class="btn btn-link p-0 ms-2" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $b->id }}" title="Delete" aria-label="Delete" style="box-shadow:none;color:inherit;">
                                <i class="fa fa-trash text-danger" style="font-size:1.1rem;"></i>
                            </button>

                            <!-- Verify Modal (success theme) -->
                            <div class="modal fade" id="verifyModal{{ $b->id }}" tabindex="-1" aria-labelledby="verifyModalLabel{{ $b->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4" style="max-width:520px;margin:auto;">
                                        <div class="modal-body text-center p-4">
                                            <div class="mb-3">
                                                <div class="mx-auto bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                                                    <i class="fa fa-check-circle text-success" style="font-size:22px;"></i>
                                                </div>
                                            </div>
                                            <h5 class="mb-2">Are you sure?</h5>
                                            <p class="text-muted small mb-4">Are you sure you want to verify this beneficiary? This will notify them by email/SMS.</p>

                                            <form method="POST" action="{{ route('pwd.verify', $b->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-success w-100 mb-2" style="border-radius:10px;">Verify Beneficiary</button>
                                            </form>

                                            <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal{{ $b->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $b->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4" style="max-width:520px;margin:auto;">
                                <div class="modal-body text-center p-4">
                                    <div class="mb-3">
                                        <div class="mx-auto bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                                            <i class="fa fa-exclamation-triangle text-danger" style="font-size:22px;"></i>
                                        </div>
                                    </div>
                                    <h5 class="mb-2">Are you sure?</h5>
                                    <p class="text-muted small mb-4">Are you sure you want to delete this beneficiary? This action cannot be undone.</p>

                                    <form method="POST" action="{{ route('pwd.delete', $b->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-danger w-100 mb-2" style="border-radius:10px;">Delete Beneficiary</button>
                                    </form>

                                    <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted">No not verified PWD beneficiaries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('pwdSearchForm');
    const resultsDiv = document.getElementById('searchPWDResults');
    form?.addEventListener('submit', function(e) {
        e.preventDefault();
        const query = document.getElementById('searchPWDInput').value.trim();
        if (!query) {
            resultsDiv.innerHTML = '<div class="alert alert-warning">Please enter a search term.</div>';
            return;
        }
        resultsDiv.innerHTML = '<div class="text-center text-muted"><span class="spinner-border spinner-border-sm"></span> Searching...</div>';
        fetch("{{ route('pwd.related-search') }}?search=" + encodeURIComponent(query), {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (Array.isArray(data.results) && data.results.length > 0) {
                resultsDiv.innerHTML = data.results.map(b => `
                    <div class="card mb-2">
                        <div class="card-body py-2">
                            <div>
                                <strong>${b.last_name}, ${b.first_name}</strong>
                                <div class="small text-muted">PWD ID: ${b.pwd_id_number ?? ''}</div>
                                <div class="small text-muted">Barangay: ${b.barangay_name ?? 'N/A'}</div>
                                <div class="small">Gender: ${b.gender ?? ''}</div>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                resultsDiv.innerHTML = '<div class="alert alert-info">No matching PWD beneficiary found.</div>';
            }
        })
        .catch(() => {
            resultsDiv.innerHTML = '<div class="alert alert-danger">Search failed. Please try again.</div>';
        });
    });
});
</script>
@endpush
