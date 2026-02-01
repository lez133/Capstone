@php use Illuminate\Support\Facades\Crypt; @endphp
@extends('layouts.adminlayout')

@section('title', 'All Beneficiaries')

@section('content')
<div class="mt-4">

    {{-- Title --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">All Beneficiaries</h5>
        <div class="d-flex gap-2">
            {{-- CSV Export --}}
            <a id="exportCSV" href="{{ route('beneficiaries.export', request()->except('page')) }}" class="btn btn-success">
                <i class="fa fa-download"></i> Export CSV
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form id="filterForm" method="GET" class="mb-3 row g-2">
        <input type="hidden" name="page" id="pageInput" value="{{ request('page', 1) }}">

        <div class="col-md-3">
            <input type="text" name="search" id="searchInput" class="form-control" placeholder="Search name or ID..." value="{{ request('search') }}">
        </div>

        <div class="col-md-2">
            <select name="barangay" class="form-select auto-submit">
                <option value="">-- Barangay --</option>
                @foreach($allBarangays as $brgy)
                    <option value="{{ $brgy->barangay_name }}" @if(request('barangay')==$brgy->barangay_name) selected @endif>{{ $brgy->barangay_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="type" class="form-select auto-submit">
                <option value="">-- Type --</option>
                <option value="Senior Citizen" @if(request('type')=='Senior Citizen') selected @endif>Senior Citizen</option>
                <option value="PWD" @if(request('type')=='PWD') selected @endif>PWD</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="gender" class="form-select auto-submit">
                <option value="">-- Gender --</option>
                <option value="Male" @if(request('gender')=='Male') selected @endif>Male</option>
                <option value="Female" @if(request('gender')=='Female') selected @endif>Female</option>
            </select>
        </div>

        <div class="col-md-3">
            <select name="remarks" class="form-select auto-submit">
                <option value="">-- Remarks --</option>
                @foreach($allRemarks as $remark)
                    <option value="{{ $remark }}" @if(request('remarks')==$remark) selected @endif>{{ $remark }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-12 mt-2">
            <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" id="beneficiaryTable">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Barangay</th>
                    <th>Type</th>
                    <th>Gender</th>
                    <th>Birthday</th>
                    <th>Age</th>
                    <th>ID Number</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paginated as $b)
                    <tr>
                        <td>{{ ($paginated->firstItem() ?? 0) + $loop->index }}</td>
                        <td>{{ $b->last_name ?? 'N/A' }}, {{ $b->first_name ?? 'N/A' }} {{ $b->middle_name ?? '' }}</td>
                        <td>{{ $b->barangay->barangay_name ?? 'N/A' }}</td>
                        <td>
                            @if($b instanceof \App\Models\SeniorCitizenBeneficiary)
                                Senior Citizen
                            @elseif($b instanceof \App\Models\PWDBeneficiary)
                                PWD
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if(isset($b->gender))
                                @if(strtoupper($b->gender) === 'M')
                                    Male
                                @elseif(strtoupper($b->gender) === 'F')
                                    Female
                                @else
                                    {{ $b->gender }}
                                @endif
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $b->birthday ?? 'N/A' }}</td>
                        <td>{{ $b->age ?? 'N/A' }}</td>
                        <td>
                            @if($b instanceof \App\Models\SeniorCitizenBeneficiary)
                                @php
                                    $idNumber = '';
                                    if ($b->osca_number) {
                                        try {
                                            $idNumber = Crypt::decryptString($b->osca_number);
                                            $idNumber = preg_replace('/^s:\d+:"([^"]+)";$/', '$1', $idNumber);
                                        } catch (\Exception $e) {
                                            $idNumber = '';
                                        }
                                    }
                                @endphp
                                {{ $idNumber ?: 'N/A' }}
                            @elseif($b instanceof \App\Models\PWDBeneficiary || (property_exists($b, 'pwd_id_number') && $b->pwd_id_number))
                                {{ $b->pwd_id_number ?: 'N/A' }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($b instanceof \App\Models\SeniorCitizenBeneficiary)
                                {{ $b->remarks ?? 'N/A' }}
                            @elseif($b instanceof \App\Models\PWDBeneficiary)
                                {{ $b->remarks ?? 'N/A' }}
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-3">No beneficiaries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination (simple Previous / Next) --}}
    <div class="d-flex justify-content-center align-items-center gap-3 my-3">
        @if($paginated->onFirstPage())
            <button class="btn btn-secondary" disabled>Previous</button>
        @else
            <a href="{{ request()->fullUrlWithQuery(['page' => $paginated->currentPage() - 1]) }}" class="btn btn-primary">Previous</a>
        @endif

        <span class="text-muted">Page {{ $paginated->currentPage() }} of {{ $paginated->lastPage() }}</span>

        @if($paginated->hasMorePages())
            <a href="{{ request()->fullUrlWithQuery(['page' => $paginated->currentPage() + 1]) }}" class="btn btn-primary">Next</a>
        @else
            <button class="btn btn-secondary" disabled>Next</button>
        @endif
    </div>

</div>
@endsection


@push('scripts')
<script>
    document.querySelectorAll('.auto-submit').forEach(el => {
        el.addEventListener('change', () => {
            document.getElementById('pageInput').value = 1;
            document.getElementById('filterForm').submit();
        });
    });

    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('pageInput').value = 1;
                document.getElementById('filterForm').submit();
            }, 500);
        });
    }
</script>
@endpush


