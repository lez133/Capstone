
<div class="modal fade" id="modal-schedule" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="#">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Schedule Distribution</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small">Distribution Type</label>
            <select name="aid_type" class="form-select" required>
              <option value="cash">Cash Aid</option>
              <option value="medical">Medical Support</option>
              <option value="pension">Pension</option>
              <option value="event">Program Event</option>
            </select>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small">Date</label>
              <input name="date" type="date" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label small">Location / Barangay</label>
              <input name="location" type="text" class="form-control" required>
            </div>

            <div class="col-12">
              <label class="form-label small">Notes</label>
              <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Schedule</button>
        </div>
      </form>
    </div>
  </div>
</div>
