@extends('layouts.adminlayout')

@section('content')
<h1 class="mb-4">Send Notifications</h1>

<div class="alert alert-info">
    <strong>Telco advisory:</strong>
    - Do not repeatedly send nearly identical messages to the same number (may be treated as spam).
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" id="notifTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="send-tab" data-bs-toggle="tab" data-bs-target="#tab-send" type="button" role="tab" aria-controls="tab-send" aria-selected="true">
      Send
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link" id="history-tab" href="{{ route('notifications.history') }}" role="tab" aria-controls="tab-history" aria-selected="false">
      History
    </a>
  </li>
</ul>

<div class="tab-content">
  <div class="tab-pane fade show active" id="tab-send" role="tabpanel" aria-labelledby="send-tab">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card p-3">
                <h5>Send SMS</h5>
                <form method="POST" action="{{ route('notifications.sms') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="recipient" class="form-label">Recipient (09XXXXXXXXX or 639XXXXXXXXX)</label>
                        <input id="recipient" name="recipient" class="form-control" value="{{ old('recipient') }}"
                            @if(old('barangay_id')) disabled @else required @endif>
                        @error('recipient') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="barangay_id" class="form-label">Or send to all verified beneficiaries in Barangay</label>
                        <select id="barangay_id" name="barangay_id" class="form-select"
                            onchange="
                                if(this.value) {
                                    document.getElementById('recipient').value='';
                                    document.getElementById('recipient').setAttribute('disabled','disabled');
                                } else {
                                    document.getElementById('recipient').removeAttribute('disabled');
                                }
                            ">
                            <option value="">-- select barangay --</option>
                            @foreach($barangays as $barangay)
                                <option value="{{ $barangay->id }}" {{ old('barangay_id') == $barangay->id ? 'selected' : '' }}>
                                    {{ $barangay->barangay_name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            Selecting a barangay will ignore the individual recipient field and send to all verified beneficiaries in that barangay.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea id="message" name="message" class="form-control" rows="4" required>{{ old('message') }}</textarea>
                        @error('message') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Send SMS</button>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-3">
                <h5>Send Email (Gmail)</h5>

                <form method="POST" action="{{ route('notifications.gmail') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="recipient_email" class="form-label">Recipient</label>
                        <select id="recipient_email" name="recipient" class="form-select" required>
                            <option value="">-- select recipient --</option>
                            <option value="all">All members (broadcast)</option>
                            @foreach($barangays as $barangay)
                                @php
                                    $verifiedBeneficiaries = $beneficiaries->where('barangay_id', $barangay->id)->where('verified', true);
                                @endphp
                                @if($verifiedBeneficiaries->count())
                                    <optgroup label="{{ $barangay->barangay_name }}">
                                        @foreach($verifiedBeneficiaries as $ben)
                                            @if(!empty($ben->email))
                                                <option value="{{ $ben->email }}" {{ old('recipient') === $ben->email ? 'selected' : '' }}>
                                                    {{ $ben->email }} - {{ $ben->first_name }} {{ $ben->last_name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                            @foreach($members as $m)
                                @if(!empty($m->email))
                                    <option value="{{ $m->email }}" {{ old('recipient') === $m->email ? 'selected' : '' }}>
                                        {{ $m->email }}
                                        @if(!empty($m->first_name) || !empty($m->last_name))
                                            - {{ trim(($m->first_name ?? '') . ' ' . ($m->last_name ?? '')) }}
                                        @endif
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('recipient') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input id="subject" name="subject" class="form-control" value="{{ old('subject') }}" required>
                        @error('subject') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email_message" class="form-label">Message</label>
                        <textarea id="email_message" name="message" class="form-control" rows="5" required>{{ old('message') }}</textarea>
                        @error('message') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Send Email</button>
                </form>
            </div>
        </div>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-history" role="tabpanel" aria-labelledby="history-tab">
    <div class="card p-3">
      <h5 class="mb-3">Notification History</h5>

      @php
        $notifications = $notifications ?? collect();
      @endphp

      @if($notifications->isEmpty())
        <div class="text-muted">No notifications found.</div>
      @else
        <div class="table-responsive">
          <table class="table table-sm table-hover">
            <thead>
              <tr>
                <th>Type</th>
                <th>Recipient</th>
                <th>Subject / Message</th>
                <th>Status</th>
                <th>Sent At</th>
              </tr>
            </thead>
            <tbody>
              @foreach($notifications as $n)
                <tr>
                  <td>{{ $n->type ?? ($n->channel ?? '-') }}</td>
                  <td>{{ $n->recipient ?? ($n->to ?? '-') }}</td>
                  <td style="max-width:420px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    @if(!empty($n->subject)) <strong>{{ $n->subject }}</strong><br>@endif
                    {{ Str::limit($n->message ?? $n->body ?? '-', 140) }}
                  </td>
                  <td>{{ $n->status ?? ($n->result ?? '-') }}</td>
                  <td>{{ optional($n->created_at)->format('Y-m-d H:i') ?? '-' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        @if(method_exists($notifications, 'links'))
          <div class="mt-2">
            {{ $notifications->links() }}
          </div>
        @endif
      @endif
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const barangaySelect = document.getElementById('barangay_id');
    const recipientInput = document.getElementById('recipient');
    if(barangaySelect && recipientInput) {
        barangaySelect.addEventListener('change', function() {
            if(this.value) {
                recipientInput.value = '';
                recipientInput.setAttribute('disabled', 'disabled');
            } else {
                recipientInput.removeAttribute('disabled');
            }
        });
    }
    const alerts = document.querySelectorAll('.alert-success, .alert-danger');
    if (!alerts.length) return;
    setTimeout(() => {
        alerts.forEach(a => {
            a.style.transition = 'opacity .4s ease, max-height .4s ease, margin .4s ease, padding .4s ease';
            a.style.opacity = '0';
            a.style.maxHeight = '0';
            a.style.margin = '0';
            a.style.padding = '0';
        });
        setTimeout(() => alerts.forEach(a => a.remove()), 450);
    }, 3000);
});
</script>
@endpush
