@extends('layouts.adminlayout')

@section('title', 'Senior Citizen Beneficiaries - ' . $barangay->barangay_name)

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Senior Citizen Beneficiaries</h1>
    <h3 class="mb-4">Barangay: {{ $barangay->barangay_name }}</h3>
    <div class="mt-4">
        <a href="{{ route('beneficiaries.interface', ['encryptedBarangayId' => encrypt($barangay->id)]) }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Barangays
        </a>
    </div>
    <!-- Search Bar & Remarks Filter -->
    <form method="GET" action="{{ route('senior-citizen-beneficiaries.view', ['encryptedBarangayId' => Crypt::encrypt($barangay->id)]) }}" class="mb-4">
        <div class="row g-2">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search beneficiaries..." value="{{ $search ?? '' }}">
            </div>

            <div class="col-md-2">
                <select name="gender" class="form-select">
                    <option value="">All Genders</option>
                    <option value="M" @if(($genderFilter ?? request('gender')) == 'M') selected @endif>Male</option>
                    <option value="F" @if(($genderFilter ?? request('gender')) == 'F') selected @endif>Female</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="civil_status" class="form-select">
                    <option value="">All Civil Status</option>
                    <option value="Married" @if(($civilStatusFilter ?? request('civil_status')) == 'Married') selected @endif>Married</option>
                    <option value="Widowed" @if(($civilStatusFilter ?? request('civil_status')) == 'Widowed') selected @endif>Widowed</option>
                    <option value="Single" @if(($civilStatusFilter ?? request('civil_status')) == 'Single') selected @endif>Single</option>
                    <option value="Separated" @if(($civilStatusFilter ?? request('civil_status')) == 'Separated') selected @endif>Separated</option>
                    <option value="Divorced" @if(($civilStatusFilter ?? request('civil_status')) == 'Divorced') selected @endif>Divorced</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="remarks" class="form-select">
                    <option value="">-- Filter by Remarks --</option>
                    @foreach ($allRemarks as $remark)
                        <option value="{{ $remark }}" @if(request('remarks') == $remark) selected @endif>{{ $remark }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i>
                </button>
            </div>
        </div>
    </form>

    <!-- Add Beneficiary Button & Export CSV -->
    <div class="mb-4 d-flex gap-2">
        <a href="{{ route('senior-citizen-beneficiaries.create', ['barangay' => encrypt($barangay->id)]) }}" class="btn btn-primary">
            <i class="fa fa-user-plus"></i> Add Senior Citizen Beneficiary
        </a>
        <a href="{{ route('senior-citizen-beneficiaries.export', ['encryptedBarangayId' => Crypt::encrypt($barangay->id)]) }}" class="btn btn-success">
            <i class="fa fa-download"></i> Export CSV
        </a>
    </div>

    @if ($beneficiaries->count() > 0)
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fa fa-users"></i> Beneficiaries List
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Birthday</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Civil Status</th>
                            <th>OSCA Number</th>
                            <th>Date Issued</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($beneficiaries as $index => $beneficiary)
                            <tr>
                                <td>{{ $loop->iteration + ($beneficiaries->currentPage() - 1) * $beneficiaries->perPage() }}</td>
                                <td>{{ $beneficiary->last_name }}</td>
                                <td>{{ $beneficiary->first_name }}</td>
                                <td>{{ $beneficiary->middle_name }}</td>
                                <td>{{ $beneficiary->birthday }}</td>
                                <td>{{ $beneficiary->age }}</td>
                                <td>
                                    @php $g = strtoupper(trim($beneficiary->gender ?? '')); @endphp
                                    @if($g === 'M') Male
                                    @elseif($g === 'F') Female
                                    @else {{ $beneficiary->gender }} @endif
                                </td>
                                <td>
                                    @php
                                        $cs = strtoupper(trim($beneficiary->civil_status ?? ''));
                                        if (in_array($cs, ['M', 'MARR', 'MARRIED'])) {
                                            $csText = 'Married';
                                        } elseif (in_array($cs, ['W', 'WIDOW', 'WIDOWED'])) {
                                            $csText = 'Widowed';
                                        } elseif (in_array($cs, ['S', 'SING', 'SINGLE'])) {
                                            $csText = 'Single';
                                        } elseif (in_array($cs, ['SP', 'SEP', 'SEPARATED'])) {
                                            $csText = 'Separated';
                                        } elseif (in_array($cs, ['D', 'DIV', 'DIVORCED'])) {
                                            $csText = 'Divorced';
                                        } else {
                                            $csText = $beneficiary->civil_status ?? 'N/A';
                                        }
                                    @endphp
                                    {{ $csText }}
                                </td>
                                <td>{{ Crypt::decrypt($beneficiary->osca_number) }}</td>
                                <td>{{ $beneficiary->date_issued }}</td>
                                <td>{{ $beneficiary->remarks }}</td>
                                <td>
                                    <!-- Edit Modal Trigger -->
                                    <button type="button"
                                            class="btn btn-sm btn-outline-warning"
                                            title="Edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editSeniorModal-{{ $beneficiary->id }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <!-- Delete Modal Trigger -->
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteSeniorModal-{{ $beneficiary->id }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Compact Pagination --}}
        @php $paginator = $beneficiaries; @endphp
        @if ($paginator->lastPage() > 1)
            <div class="mt-4 d-flex justify-content-center">
                <nav aria-label="Pagination">
                    <ul style="list-style:none;padding:0;margin:0;display:flex;gap:6px;align-items:center;">
                        {{-- Previous --}}
                        @if ($paginator->onFirstPage())
                            <li><span style="opacity:.6;padding:6px 12px;border-radius:4px;background:#f4f4f4;">‹ Prev</span></li>
                        @else
                            <li><a href="{{ $paginator->previousPageUrl() }}" style="text-decoration:none;padding:6px 12px;border-radius:4px;background:#fff;border:1px solid #e0e0e0;color:inherit;">‹ Prev</a></li>
                        @endif

                        {{-- Page window (current ±2) --}}
                        @php
                            $window = 2;
                            $start = max(1, $paginator->currentPage() - $window);
                            $end = min($paginator->lastPage(), $paginator->currentPage() + $window);
                        @endphp

                        @if ($start > 1)
                            <li><a href="{{ $paginator->url(1) }}" style="text-decoration:none;padding:6px 12px;border-radius:4px;background:#fff;border:1px solid #e0e0e0;">1</a></li>
                            @if ($start > 2)
                                <li><span style="padding:6px 12px;">…</span></li>
                            @endif
                        @endif

                        @for ($page = $start; $page <= $end; $page++)
                            @if ($page == $paginator->currentPage())
                                <li><span aria-current="page" style="font-weight:600;padding:6px 12px;border-radius:4px;background:#0d6efd;color:#fff;">{{ $page }}</span></li>
                            @else
                                <li><a href="{{ $paginator->url($page) }}" style="text-decoration:none;padding:6px 12px;border-radius:4px;background:#fff;border:1px solid #e0e0e0;color:inherit;">{{ $page }}</a></li>
                            @endif
                        @endfor

                        @if ($end < $paginator->lastPage())
                            @if ($end < $paginator->lastPage() - 1)
                                <li><span style="padding:6px 12px;">…</span></li>
                            @endif
                            <li><a href="{{ $paginator->url($paginator->lastPage()) }}" style="text-decoration:none;padding:6px 12px;border-radius:4px;background:#fff;border:1px solid #e0e0e0;">{{ $paginator->lastPage() }}</a></li>
                        @endif

                        {{-- Next --}}
                        @if ($paginator->hasMorePages())
                            <li><a href="{{ $paginator->nextPageUrl() }}" style="text-decoration:none;padding:6px 12px;border-radius:4px;background:#fff;border:1px solid #e0e0e0;color:inherit;">Next ›</a></li>
                        @else
                            <li><span style="opacity:.6;padding:6px 12px;border-radius:4px;background:#f4f4f4;">Next ›</span></li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
    @else
        <p class="text-muted">No beneficiaries found for this barangay.</p>
    @endif

    {{-- Include the modals partial --}}
    @include('partials.Modals.senior-citizen-edit-delete', ['beneficiaries' => $beneficiaries])
</div>
@endsection
