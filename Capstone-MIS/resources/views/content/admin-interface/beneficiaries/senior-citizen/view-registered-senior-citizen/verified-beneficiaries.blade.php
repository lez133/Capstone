@extends('layouts.adminlayout')

@section('title', 'Verified Senior Citizen Beneficiaries')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Verified Beneficiaries</h1>
    <h4 class="mb-2">Barangay: {{ $barangay->barangay_name ?? 'N/A' }}</h4>

    {{-- Success flash --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- SMS debug message (only SMS debug shown, no email debug) --}}
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
    <!-- Export Button -->
    <a href="{{ route('senior-citizens.export', $encryptedBarangayId) }}" class="btn btn-success mb-3">
        <i class="fa fa-download"></i> Export CSV
    </a>

    <!-- Search Bar -->
    <div class="mb-4">
        <form method="GET" action="{{ route('senior-citizens.verified', $encryptedBarangayId) }}">
            <div class="input-group">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by name or OSCA number..."
                    value="{{ request('search') }}"
                >
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
    </div>

    @if ($verifiedBeneficiaries->isEmpty())
        <p class="text-muted">No verified beneficiaries found.</p>
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
                    @foreach ($verifiedBeneficiaries as $index => $beneficiary)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $beneficiary->last_name }}, {{ $beneficiary->first_name }}</td>
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
                                @if($beneficiary->assisted_by)
                                    @php
                                        $rep = \App\Models\MswdMember::find($beneficiary->assisted_by);
                                    @endphp
                                    {{ $rep ? $rep->fname . ' ' . $rep->lname : 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                {{-- Disable (icon-only) --}}
                                <form method="POST" action="{{ route('senior-citizens.disable', encrypt($beneficiary->id)) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to disable verification for this beneficiary?');">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0" title="Disable" aria-label="Disable">
                                        <i class="fa fa-ban text-warning"></i>
                                    </button>
                                </form>

                                {{-- Delete trigger opens modal (no inline delete form) --}}
                                <button type="button" class="btn btn-link p-0 ms-2" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $beneficiary->id }}" title="Delete" aria-label="Delete">
                                    <i class="fa fa-trash text-danger"></i>
                                </button>

                                {{-- Delete Confirmation Modal --}}
                                <div class="modal fade" id="deleteModal{{ $beneficiary->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $beneficiary->id }}" aria-hidden="true">
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
            @foreach ($verifiedBeneficiaries as $index => $beneficiary)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $beneficiary->last_name }}, {{ $beneficiary->first_name }}</h6>
                                <div class="small text-muted">{{ $beneficiary->email ?? 'N/A' }}</div>
                                <div class="small text-muted">Contact: {{ $beneficiary->phone ?? 'N/A' }}</div>
                                <div class="small mt-1">
                                    Age: {{ $beneficiary->age }} &middot;
                                    Gender: @php $g = strtoupper(trim($beneficiary->gender ?? '')); @endphp
                                    @if($g === 'M') Male @elseif($g === 'F') Female @else {{ $beneficiary->gender }} @endif
                                </div>
                                <div class="small text-muted mt-1">OSCA: {{ $beneficiary->osca_number }}</div>
                                <div class="small text-muted">Status: {{ $beneficiary->civil_status }}</div>
                                <div class="small text-muted">
                                    Assisted By:
                                    @if($beneficiary->assisted_by)
                                        @php
                                            $rep = \App\Models\MswdMember::find($beneficiary->assisted_by);
                                        @endphp
                                        {{ $rep ? $rep->fname . ' ' . $rep->lname : 'N/A' }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                <form method="POST" action="{{ route('senior-citizens.disable', $beneficiary->id) }}" class="mb-1">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0" title="Disable" aria-label="Disable" onclick="return confirm('Are you sure you want to disable verification for this beneficiary?')">
                                        <i class="fa fa-ban text-warning"></i>
                                    </button>
                                </form>
                                <div class="btn-group-vertical btn-group-sm" role="group" aria-label="actions">
                                    <form method="POST" action="{{ route('senior-citizens.delete', $beneficiary->id) }}" class="p-0 mb-1" onsubmit="return confirm('Are you sure you want to delete this beneficiary? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
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

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            @if(method_exists($verifiedBeneficiaries, 'links'))
                {{ $verifiedBeneficiaries->links() }}
            @endif
        </div>
    @endif
</div>
@endsection
