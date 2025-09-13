<div class="modal fade" id="modal-register" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="#" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Register Beneficiary</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small">Full Name</label>
              <input name="name" type="text" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label small">Type</label>
              <select name="type" class="form-select" required>
                <option value="PWD">PWD</option>
                <option value="Senior">Senior Citizen</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small">Barangay</label>
              <input name="barangay" type="text" class="form-control" required>
            </div>

            <div class="col-md-4">
              <label class="form-label small">Birthdate</label>
              <input name="birthdate" type="date" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label small">Contact No.</label>
              <input name="contact" type="text" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label small">ID / Reference No.</label>
              <input name="ref_id" type="text" class="form-control">
            </div>

            <div class="col-12">
              <label class="form-label small">Supporting Documents (PDF / image)</label>
              <input name="documents[]" type="file" class="form-control" multiple>
            </div>

            <div class="col-12">
              <label class="form-label small">Notes</label>
              <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Register</button>
        </div>
      </form>
    </div>
  </div>
</div>
