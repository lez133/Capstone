@extends('layouts.adminlayout')

@section('title', 'Not Verified Beneficiaries')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Not Verified Beneficiaries</h1>
    <h4 class="mb-2">Barangay: {{ $barangay->barangay_name ?? 'N/A' }}</h4>

    {{-- Success / Error flashes --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- SMS debug message (if any) --}}
    @if(session('sms_debug'))
        <div class="alert alert-info">
            <strong>SMS Debug:</strong>
            <pre class="mb-0 small">{{ session('sms_debug') }}</pre>
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('senior-citizens.manage', $encryptedBarangayId) }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Manage Beneficiaries
        </a>
    </div>
    <br>
    <!-- Search Form -->
    <form method="GET" action="{{ route('senior-citizens.not-verified', $encryptedBarangayId) }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by name or OSCA number" value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">
                <i class="fa fa-search"></i> Search
            </button>
        </div>
    </form>

    <!-- Search & Match Modal Trigger Button -->
    <button type="button" class="btn btn-info btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#searchSeniorModal">
        <i class="fa fa-search"></i> Search Related Senior Citizen
    </button>

    <!-- Search & Match Modal -->
    <div class="modal fade" id="searchSeniorModal" tabindex="-1" aria-labelledby="searchSeniorModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
          <div class="modal-header">
            <h5 class="modal-title" id="searchSeniorModalLabel">
                <i class="fa fa-search"></i> Find Registered Senior Citizen
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="seniorSearchForm" autocomplete="off">
              <div class="mb-3">
                <label for="searchSeniorInput" class="form-label">Search by Name or OSCA Number</label>
                <input type="text" class="form-control" id="searchSeniorInput" name="search" placeholder="Enter name or OSCA number">
              </div>
              <button type="submit" class="btn btn-primary w-100">
                <i class="fa fa-search"></i> Search
              </button>
            </form>
            <div id="searchSeniorResults" class="mt-3"></div>
          </div>
        </div>
      </div>
    </div>

    @if ($notVerifiedBeneficiaries->isEmpty())
        <p class="text-muted">No not verified beneficiaries found.</p>
    @else

        {{-- Desktop / Tablet: table (md and up) --}}
        <div class="d-none d-md-block table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>OSCA Number</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Civil Status</th>
                        <th>Assisted By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($notVerifiedBeneficiaries as $index => $beneficiary)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $beneficiary->last_name }}, {{ $beneficiary->first_name }} {{ $beneficiary->suffix }}</td>
                            <td>{{ $beneficiary->osca_number }}</td>
                            <td>{{ $beneficiary->email ?? 'N/A' }}</td>
                            <td>{{ $beneficiary->phone ?? 'N/A' }}</td>
                            <td>{{ $beneficiary->age }}</td>
                            <td>
                                @php $g = strtoupper(trim($beneficiary->gender ?? '')); @endphp
                                @if($g === 'M') Male
                                @elseif($g === 'F') Female
                                @else {{ $beneficiary->gender }} @endif
                            </td>
                            <td>{{ $beneficiary->civil_status }}</td>
                            <td>
                                {{ $beneficiary->assisted_by && $beneficiary->assisted_by !== 'N/A' ? $beneficiary->assisted_by : 'N/A' }}
                            </td>
                            <td>
                                <form method="POST" action="{{ route('senior-citizens.verify', encrypt($beneficiary->id)) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0" title="Verify" aria-label="Verify">
                                        <i class="fa fa-check-circle text-success"></i>
                                    </button>
                                </form>

                                {{-- Delete trigger opens modal --}}
                                <button type="button" class="btn btn-link p-0 ms-2" data-bs-toggle="modal" data-bs-target="#deleteModalNot{{ $beneficiary->id }}" title="Delete" aria-label="Delete">
                                    <i class="fa fa-trash text-danger"></i>
                                </button>

                                {{-- Delete Modal --}}
                                <div class="modal fade" id="deleteModalNot{{ $beneficiary->id }}" tabindex="-1" aria-labelledby="deleteModalNotLabel{{ $beneficiary->id }}" aria-hidden="true">
                                  <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4">
                                      <div class="modal-body text-center p-4">
                                        <div class="mb-3">
                                          <div class="mx-auto bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                                            <i class="fa fa-exclamation-triangle text-danger" style="font-size:22px;"></i>
                                          </div>
                                        </div>
                                        <h5 class="mb-2">Are you sure?</h5>
                                        <p class="text-muted small mb-4">Are you sure you want to delete this beneficiary? This action cannot be undone.</p>

                                        <form method="POST" action="{{ route('senior-citizens.delete', encrypt($beneficiary->id)) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100 mb-2">Delete Beneficiary</button>
                                        </form>

                                        <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Cancel</button>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile: stacked cards (sm and below) --}}
        <div class="d-block d-md-none">
            @foreach ($notVerifiedBeneficiaries as $index => $beneficiary)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $beneficiary->last_name }}, {{ $beneficiary->first_name }}</h6>
                                <div class="small text-muted">{{ $beneficiary->email }}</div>
                                <div class="small text-muted">Contact: {{ $beneficiary->phone ?? 'N/A' }}</div>
                                <div class="small mt-1">
                                    Age: {{ $beneficiary->age }} &middot;
                                    Gender: @php $g = strtoupper(trim($beneficiary->gender ?? '')); @endphp
                                    @if($g === 'M') Male @elseif($g === 'F') Female @else {{ $beneficiary->gender }} @endif
                                </div>
                                <div class="small text-muted mt-1">OSCA: {{ $beneficiary->osca_number }}</div>
                                <div class="small text-muted">Status: {{ $beneficiary->civil_status }}</div>
                                <div class="small text-muted">Assisted By: {{ $beneficiary->assisted_by && $beneficiary->assisted_by !== 'N/A' ? $beneficiary->assisted_by : 'N/A' }}</div>
                            </div>
                            <div class="text-end">
                                <form method="POST" action="{{ route('senior-citizens.verify', encrypt($beneficiary->id)) }}" class="mb-1">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0" title="Verify" aria-label="Verify">
                                        <i class="fa fa-check-circle text-success"></i>
                                    </button>
                                </form>
                                <div class="btn-group-vertical btn-group-sm" role="group" aria-label="actions">
                                    <button class="btn btn-link p-0 mb-1" data-bs-toggle="modal" data-bs-target="#editModal{{ $beneficiary->id }}" title="Edit" aria-label="Edit">
                                        <i class="fa fa-edit text-info"></i>
                                    </button>
                                    <form method="POST" action="{{ route('senior-citizens.delete', encrypt($beneficiary->id)) }}" class="p-0 mb-1" onsubmit="return confirm('Are you sure you want to delete this beneficiary? This action cannot be undone.');">
                                        @csrf
                                        <button type="submit" class="btn btn-link p-0" title="Delete" aria-label="Delete">
                                            <i class="fa fa-trash text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    @endif
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('seniorSearchForm');
    const resultsDiv = document.getElementById('searchSeniorResults');
    form?.addEventListener('submit', function(e) {
        e.preventDefault();
        const query = document.getElementById('searchSeniorInput').value.trim();
        if (!query) {
            resultsDiv.innerHTML = '<div class="alert alert-warning">Please enter a search term.</div>';
            return;
        }
        resultsDiv.innerHTML = '<div class="text-center text-muted"><span class="spinner-border spinner-border-sm"></span> Searching...</div>';
        fetch("{{ route('senior-citizens.related-search') }}?search=" + encodeURIComponent(query), {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (Array.isArray(data.results) && data.results.length > 0) {
                resultsDiv.innerHTML = data.results.map(b => `
                    <div class="card mb-2">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${b.last_name}, ${b.first_name}</strong>
                                    <div class="small text-muted">OSCA: ${b.osca_number ?? ''}</div>
                                    <div class="small text-muted">Barangay: ${b.barangay_name ?? 'N/A'}</div>
                                    <div class="small">Age: ${b.age ?? ''} | Gender: ${b.gender ?? ''}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                resultsDiv.innerHTML = '<div class="alert alert-info">No matching senior citizen found.</div>';
            }
        })
        .catch(() => {
            resultsDiv.innerHTML = '<div class="alert alert-danger">Search failed. Please try again.</div>';
        });
    });
});
</script>
@endpush
