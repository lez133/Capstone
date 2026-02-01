@extends('layouts.adminlayout')
@section('title', 'Verified PWD  Beneficiaries')
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

    <h4 class="mb-3">Verified PWD Beneficiaries - {{ $barangay->barangay_name }}</h4>

    {{-- Search form --}}
    <form method="GET" action="{{ route('pwd.verified', $encryptedBarangayId) }}" class="mb-3">
        <div class="input-group" style="max-width:480px;">
            <input type="text" name="search" class="form-control" placeholder="Search by name, PWD ID, or email" value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">
                <i class="fa fa-search"></i> Search
            </button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>PWD ID Number</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Civil Status</th>
                    <th>Assisted By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($verifiedBeneficiaries as $index => $b)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            {{ $b->last_name }}, {{ $b->first_name }} {{ $b->suffix }}
                        </td>
                        <td>{{ $b->pwd_id ?: 'N/A' }}</td>
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
                            <span class="badge bg-success">Verified</span>
                        </td>
                        <td>
                            {{-- Disable icon-only (opens modal) --}}
                            <button class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#disableModal{{ $b->id }}" title="Disable" aria-label="Disable" style="box-shadow:none;color:inherit;">
                                <i class="fa fa-ban text-warning" style="font-size:1.05rem;"></i>
                            </button>

                            {{-- Delete icon-only (opens modal) --}}
                            <button class="btn btn-link p-0 ms-2" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $b->id }}" title="Delete" aria-label="Delete" style="box-shadow:none;color:inherit;">
                                <i class="fa fa-trash text-danger" style="font-size:1.05rem;"></i>
                            </button>

                            <!-- Disable Modal (warning theme) -->
                            <div class="modal fade" id="disableModal{{ $b->id }}" tabindex="-1" aria-labelledby="disableModalLabel{{ $b->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4" style="max-width:520px;margin:auto;">
                                        <div class="modal-body text-center p-4">
                                            <div class="mb-3">
                                                <div class="mx-auto bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                                                    <i class="fa fa-ban text-warning" style="font-size:22px;"></i>
                                                </div>
                                            </div>
                                            <h5 class="mb-2">Are you sure?</h5>
                                            <p class="text-muted small mb-4">Are you sure you want to disable verification for this beneficiary? This action can be undone.</p>

                                            <form method="POST" action="{{ route('pwd.disable', $b->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-warning w-100 mb-2" style="border-radius:10px;">Disable Beneficiary</button>
                                            </form>

                                            <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

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
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted">No verified PWD beneficiaries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
