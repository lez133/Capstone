@foreach ($beneficiaries as $beneficiary)
    <!-- Edit Modal -->
    <div class="modal fade" id="editSeniorModal-{{ $beneficiary->id }}" tabindex="-1" aria-labelledby="editSeniorModalLabel-{{ $beneficiary->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('senior-citizen-beneficiaries.update', ['id' => $beneficiary->id]) }}" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editSeniorModalLabel-{{ $beneficiary->id }}">Edit Senior Citizen Beneficiary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="{{ $beneficiary->last_name }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="{{ $beneficiary->first_name }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" value="{{ $beneficiary->middle_name }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Birthday</label>
                        <input type="date" name="birthday" class="form-control" value="{{ $beneficiary->birthday }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Age</label>
                        <input type="number" name="age" class="form-control" value="{{ $beneficiary->age }}" min="60" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="Male" @if($beneficiary->gender == 'Male') selected @endif>Male</option>
                            <option value="Female" @if($beneficiary->gender == 'Female') selected @endif>Female</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Civil Status</label>
                        <input type="text" name="civil_status" class="form-control" value="{{ $beneficiary->civil_status }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">OSCA Number</label>
                        <input type="text" name="osca_number" class="form-control" value="{{ Crypt::decrypt($beneficiary->osca_number) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Issued</label>
                        <input type="date" name="date_issued" class="form-control" value="{{ $beneficiary->date_issued }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="{{ $beneficiary->remarks }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">National ID</label>
                        <input type="text" name="national_id" class="form-control" value="{{ $beneficiary->national_id ? Crypt::decrypt($beneficiary->national_id) : '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">PKN</label>
                        <input type="text" name="pkn" class="form-control" value="{{ $beneficiary->pkn ? Crypt::decrypt($beneficiary->pkn) : '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">RRN</label>
                        <input type="text" name="rrn" class="form-control" value="{{ $beneficiary->rrn ? Crypt::decrypt($beneficiary->rrn) : '' }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteSeniorModal-{{ $beneficiary->id }}" tabindex="-1" aria-labelledby="deleteSeniorModalLabel-{{ $beneficiary->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 p-3">
                <form method="POST" action="{{ route('senior-citizen-beneficiaries.delete', ['id' => $beneficiary->id]) }}">
                    @csrf
                    @method('DELETE')

                    <div class="modal-body text-center">
                        <div class="mb-3">
                            <span class="d-inline-block bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="fa fa-exclamation-triangle text-danger fa-2x"></i>
                            </span>
                        </div>

                        <h4 class="fw-bold mb-1">Are you sure?</h4>
                        <p class="text-muted small mb-4">Are you sure you want to delete this beneficiary? This action cannot be undone.</p>

                        <div class="text-start mb-3 small">
                            <div><strong>Name:</strong> {{ trim(implode(' ', array_filter([$beneficiary->last_name ?? '', $beneficiary->first_name ?? '', $beneficiary->middle_name ?? '']))) }}</div>
                            <div><strong>OSCA #:</strong> {{ $beneficiary->osca_number ? '••••••••' : '-' }}</div>
                            <div><strong>Contact:</strong> {{ $beneficiary->contact_no ?? $beneficiary->mobile ?? '-' }}</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg">Delete Beneficiary</button>
                            <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
