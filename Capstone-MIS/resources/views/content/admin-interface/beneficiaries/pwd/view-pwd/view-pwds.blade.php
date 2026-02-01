@extends('layouts.adminlayout')

@section('title', 'PWD Beneficiaries - ' . $barangay->barangay_name)

@section('content')
<div class="container py-4">
    <h1 class="mb-4">PWD Beneficiaries</h1>
    <h3 class="mb-4">Barangay: {{ $barangay->barangay_name }}</h3>

    {{-- Display validation errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Update failed:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Display session error (duplicate, etc.) --}}
    @if (session('error'))
        <div class="alert alert-danger">
            <strong>Update failed:</strong> {{ session('error') }}
        </div>
    @endif

    {{-- Display success message --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('beneficiaries.interface', ['encryptedBarangayId' => encrypt($barangay->id)]) }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Barangays
        </a>
    </div>

    <!-- Search Bar & Filters -->
    <form method="GET" action="{{ route('pwd.view', ['encryptedBarangayId' => Crypt::encrypt($barangay->id)]) }}" class="mb-4">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search beneficiaries..." value="{{ $search ?? '' }}">
            </div>
            <div class="col-md-3">
                <select name="type_of_disability" class="form-select">
                    <option value="">-- Filter by Disability --</option>
                    @foreach ($allDisabilities as $disability)
                        <option value="{{ $disability }}" @if(request('type_of_disability') == $disability) selected @endif>{{ $disability }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="validity_years" class="form-select">
                    <option value="">-- Filter by Validity Years --</option>
                    @foreach ($allValidityYears as $year)
                        <option value="{{ $year }}" @if(request('validity_years') == $year) selected @endif>{{ $year }}</option>
                    @endforeach
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
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-search"></i> Search
                </button>
            </div>
        </div>
    </form>

    <!-- Add Beneficiary & Export -->
    <div class="mb-4 d-flex gap-2">
        <a href="{{ route('pwd.create', ['barangay' => encrypt($barangay->id)]) }}" class="btn btn-primary">
            <i class="fa fa-user-plus"></i> Add PWD Beneficiary
        </a>
        <a href="#" class="btn btn-success">
            <i class="fa fa-download"></i> Export CSV
        </a>
    </div>

    @if ($beneficiaries->count() > 0)
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fa fa-wheelchair"></i> Beneficiaries List
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Gender</th>
                            <th>Birthday</th>
                            <th>Age</th>
                            <th>Type of Disability</th>
                            <th>ID Number</th>
                            <th>Validity Years</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($beneficiaries as $index => $b)
                            <tr>
                                <td>{{ $loop->iteration + ($beneficiaries->currentPage() - 1) * $beneficiaries->perPage() }}</td>
                                <td>{{ $b->last_name ?? 'N/A' }}</td>
                                <td>{{ $b->first_name ?? 'N/A' }}</td>
                                <td>{{ $b->middle_name ?? 'N/A' }}</td>
                                <td>
                                    @php $g = strtoupper(trim($b->gender ?? '')); @endphp
                                    @if($g === 'M') Male
                                    @elseif($g === 'F') Female
                                    @else {{ $b->gender ?? 'N/A' }} @endif
                                </td>
                                <td>{{ $b->birthday ?? 'N/A' }}</td>
                                <td>{{ $b->age ?? 'N/A' }}</td>
                                <td>{{ $b->type_of_disability ?? 'N/A' }}</td>
                                <td>{{ $b->pwd_id_number ?? 'N/A' }}</td>
                                <td>{{ $b->validity_years ?? 'N/A' }}</td>
                                <td>{{ $b->remarks ?? 'N/A' }}</td>
                                <td>
                                    <!-- Edit (open modal) - outline style (no fill) -->
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-warning btn-edit-modal"
                                        title="Edit"
                                        data-id="{{ $b->id }}"
                                    >
                                        <i class="fa fa-edit text-warning"></i>
                                    </button>

                                    <!-- Delete (open modal) - outline style (no fill) -->
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger btn-delete-modal"
                                        title="Delete"
                                        data-id="{{ $b->id }}"
                                        data-name="{{ $b->last_name }}, {{ $b->first_name }}"
                                    >
                                        <i class="fa fa-trash text-danger"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination (custom, non-Bootstrap) -->
        @php $paginator = $beneficiaries; @endphp
        @if ($paginator->lastPage() > 1)
            <div class="mt-4 d-flex justify-content-center">
                <nav aria-label="Pagination">
                    <ul style="list-style:none;padding:0;margin:0;display:flex;gap:6px;align-items:center;">
                        {{-- Prev --}}
                        @if ($paginator->onFirstPage())
                            <li><span style="opacity:.6;padding:6px 10px;border-radius:4px;background:#f4f4f4;">« Prev</span></li>
                        @else
                            <li><a href="{{ $paginator->previousPageUrl() }}" style="text-decoration:none;padding:6px 10px;border-radius:4px;background:#fff;border:1px solid #e0e0e0;color:inherit;">« Prev</a></li>
                        @endif

                        {{-- Page window (current ±2) --}}
                        @php
                            $start = max(1, $paginator->currentPage() - 2);
                            $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
                        @endphp

                        @if ($start > 1)
                            <li><a href="{{ $paginator->url(1) }}" style="text-decoration:none;padding:6px 10px;border-radius:4px;background:#fff;border:1px solid #e0e0e0;">1</a></li>
                            @if ($start > 2)
                                <li><span style="padding:6px 10px;">…</span></li>
                            @endif
                        @endif

                        @for ($page = $start; $page <= $end; $page++)
                            @if ($page == $paginator->currentPage())
                                <li><span aria-current="page" style="font-weight:600;padding:6px 10px;border-radius:4px;background:#0d6efd;color:#fff;">{{ $page }}</span></li>
                            @else
                                <li><a href="{{ $paginator->url($page) }}" style="text-decoration:none;padding:6px 10px;border-radius:4px;background:#fff;border:1px solid #e0e0e0;color:inherit;">{{ $page }}</a></li>
                            @endif
                        @endfor

                        @if ($end < $paginator->lastPage())
                            @if ($end < $paginator->lastPage() - 1)
                                <li><span style="padding:6px 10px;">…</span></li>
                            @endif
                            <li><a href="{{ $paginator->url($paginator->lastPage()) }}" style="text-decoration:none;padding:6px 10px;border-radius:4px;background:#fff;border:1px solid #e0e0e0;">{{ $paginator->lastPage() }}</a></li>
                        @endif

                        {{-- Next --}}
                        @if ($paginator->hasMorePages())
                            <li><a href="{{ $paginator->nextPageUrl() }}" style="text-decoration:none;padding:6px 10px;border-radius:4px;background:#fff;border:1px solid #e0e0e0;color:inherit;">Next »</a></li>
                        @else
                            <li><span style="opacity:.6;padding:6px 10px;border-radius:4px;background:#f4f4f4;">Next »</span></li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
    @else
        <p class="text-muted">No PWD beneficiaries found for this barangay.</p>
    @endif
</div>

<!-- Edit Beneficiary Modal (unchanged fields) -->
<div class="modal fade" id="editBeneficiaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editBeneficiaryForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit PWD Beneficiary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <input type="hidden" name="id" id="modal_beneficiary_id">
                        <!-- form fields same as before -->
                        <div class="col-md-4">
                            <label class="form-label">Last Name</label>
                            <input name="last_name" id="modal_last_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First Name</label>
                            <input name="first_name" id="modal_first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input name="middle_name" id="modal_middle_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" id="modal_gender" class="form-select" required>
                                <option value="">Select</option>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birthday</label>
                            <input type="date" name="birthday" id="modal_birthday" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Age</label>
                            <input type="number" name="age" id="modal_age" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type of Disability</label>
                            <input name="type_of_disability" id="modal_type_of_disability" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ID Number</label>
                            <input name="pwd_id_number" id="modal_pwd_id_number" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Validity Years</label>
                            <input type="number" name="validity_years" id="modal_validity_years" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Remarks</label>
                            <input name="remarks" id="modal_remarks" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Barangay</label>
                            <select name="barangay_id" id="modal_barangay_id" class="form-select" required>
                                @foreach($barangay->getConnection()->table('barangays')->get() as $brgyOption)
                                    <option value="{{ $brgyOption->id }}">{{ $brgyOption->barangay_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal (unchanged) -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteBeneficiaryForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <div style="width:56px;height:56px;border-radius:12px;background:#fdecea;margin:0 auto;display:flex;align-items:center;justify-content:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#d63a2a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L9.18 2.75A2 2 0 0 0 7.11 2H4"></path><path d="M20.49 20.49a2.1 2.1 0 0 1-1.49.61H5"></path><circle cx="12" cy="12" r="10"></circle><path d="M9 9l6 6M15 9l-6 6"></path></svg>
                        </div>
                    </div>

                    <h5 class="mb-2">Are you sure?</h5>
                    <p class="text-muted mb-4">Are you sure you want to delete this beneficiary? This action cannot be undone.</p>

                    <p class="fw-semibold" id="deleteBeneficiaryName"></p>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger">Delete Beneficiary</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // base URLs for update/delete/json (assumes routes under /beneficiaries/pwd)
    const base = "{{ url('beneficiaries/pwd') }}";

    // Edit modal: fetch details from server and populate modal
    document.querySelectorAll('.btn-edit-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const form = document.getElementById('editBeneficiaryForm');
            // set form action (PUT to /beneficiaries/pwd/{id})
            form.action = base + '/' + id;

            // fetch JSON details
            fetch(base + '/' + id + '/json', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => {
                if (!res.ok) throw new Error('Failed to fetch beneficiary');
                return res.json();
            })
            .then(data => {
                document.getElementById('modal_beneficiary_id').value = data.id;
                document.getElementById('modal_last_name').value = data.last_name || '';
                document.getElementById('modal_first_name').value = data.first_name || '';
                document.getElementById('modal_middle_name').value = data.middle_name || '';
                document.getElementById('modal_gender').value = data.gender || '';
                document.getElementById('modal_birthday').value = data.birthday ? data.birthday.split(' ')[0] : '';
                document.getElementById('modal_age').value = data.age || '';
                document.getElementById('modal_type_of_disability').value = data.type_of_disability || '';
                document.getElementById('modal_pwd_id_number').value = data.pwd_id_number || '';
                document.getElementById('modal_validity_years').value = data.validity_years || '';
                document.getElementById('modal_remarks').value = data.remarks || '';
                if (data.barangay_id) document.getElementById('modal_barangay_id').value = data.barangay_id;
                const modal = new bootstrap.Modal(document.getElementById('editBeneficiaryModal'));
                modal.show();
            })
            .catch(err => {
                console.error(err);
                alert('Unable to load beneficiary details.');
            });
        });
    });

    // Delete modal: populate and open (unchanged)
    document.querySelectorAll('.btn-delete-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const name = this.dataset.name || '';
            document.getElementById('deleteBeneficiaryName').textContent = name;

            const form = document.getElementById('deleteBeneficiaryForm');
            form.action = base + '/' + id;

            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            modal.show();
        });
    });
});
</script>
@endpush
@endsection
