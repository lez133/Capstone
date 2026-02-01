@extends('layouts.brgylayout')

@section('title', 'Send Notification')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 fw-bold">Send Notification</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row gx-4">
        <!-- SMS form -->
        <div class="col-md-6">
            <form method="POST" action="{{ route('brgyrep.notifications.sendSms') }}" class="card shadow p-4">
                @csrf
                <h5 class="mb-3">Send SMS</h5>

                <div class="mb-3">
                    <label for="mswd_member_id_sms" class="form-label fw-semibold">Select MSWD Member <span class="text-danger">*</span></label>
                    <select name="mswd_member_id" id="mswd_member_id_sms" class="form-select @error('mswd_member_id') is-invalid @enderror" required>
                        <option value="">-- Select Member --</option>
                        @foreach($mswd_members as $member)
                            <option value="{{ $member->id }}" {{ old('mswd_member_id') == $member->id ? 'selected' : '' }}>
                                {{ $member->getFullNameAttribute() }} ({{ $member->contact }})
                            </option>
                        @endforeach
                    </select>
                    @error('mswd_member_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="message_sms" class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                    <textarea name="message" id="message_sms" rows="4" class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                    @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="fas fa-sms me-2"></i>Send SMS
                </button>
            </form>
        </div>

        <!-- Email (Gmail) form -->
        <div class="col-md-6">
            <!-- add id so JS can select the form without resolving routes in Blade -->
            <form id="emailForm" method="POST" action="{{ route('brgyrep.notifications.sendEmail') }}" class="card shadow p-4">
                @csrf
                <h5 class="mb-3">Send Email (Gmail)</h5>

                <div class="mb-3">
                    <label for="recipient_email" class="form-label fw-semibold">Recipient (or select member)</label>
                    <div class="d-flex gap-2">
                        <!-- give the select the name so it's submitted -->
                        <select id="member_select_email" name="recipient" class="form-select">
                            <option value="">-- Select Member Email --</option>
                            @foreach($mswd_members as $member)
                                @if(!empty($member->email))
                                    <option value="{{ $member->email }}">{{ $member->getFullNameAttribute() }} — {{ $member->email }}</option>
                                @endif
                            @endforeach
                        </select>

                        <!-- make this an optional helper input (remove its name so it doesn't conflict) -->
                        <input type="email" id="recipient_email" class="form-control" placeholder="or type email@example.com" value="{{ old('recipient') }}">
                    </div>
                    @error('recipient')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="subject_email" class="form-label fw-semibold">Subject</label>
                    <input id="subject_email" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required>
                    @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="message_email" class="form-label fw-semibold">Message</label>
                    <textarea name="message" id="message_email" rows="5" class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                    @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-success w-100 fw-semibold">
                    <i class="fas fa-envelope me-2"></i>Send Email
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const memberSelect = document.getElementById('member_select_email');
    const recipientInput = document.getElementById('recipient_email'); // helper input

    memberSelect?.addEventListener('change', function () {
        if (this.value) recipientInput.value = this.value;
    });

    // ensure select value copies before submit (select form by ID — safe, no route() call)
    const emailForm = document.getElementById('emailForm');
    emailForm?.addEventListener('submit', function () {
        if (memberSelect && memberSelect.value) recipientInput.value = memberSelect.value;
    });
});
</script>
@endpush
