@extends('layouts.beneficiarieslayout')

@section('title', 'Profile Management')

@section('content')
<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
  <style>
    :root{
      --card-bg: #ffffff;
      --muted: #6c757d;
      --accent: #ff6b21;
      --soft: #f6f9ff;
      --shadow: 0 6px 20px rgba(18,38,63,.08);
      --radius: .6rem;
    }

    /* Header background */
    .profile-header {
      height: 160px;
      background-image: url('{{ asset("img/beneficiaries-account-background.jpeg") }}');
      background-size: cover;
      background-position: center center;
      border-top-left-radius: .75rem;
      border-top-right-radius: .75rem;
      position: relative;
      overflow: hidden;
      box-shadow: var(--shadow);
    }
    .profile-header::after{
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(0,0,0,0.08), rgba(0,0,0,0.02));
      pointer-events: none;
    }

    /* Cards */
    .profile-card, .panel-card {
      border: none;
      border-radius: .75rem;
      background: var(--card-bg);
      box-shadow: var(--shadow);
    }

    /* Avatar */
    .profile-avatar-wrap{
      width:128px;
      height:128px;
      border-radius:50%;
      border:6px solid #fff;
      margin-top:-64px;
      overflow:hidden;
      background:linear-gradient(180deg,#eef3ff,#fff);
      position:relative;
      z-index:3;
      transition: transform .12s ease;
      cursor: pointer;
    }
    .profile-avatar-wrap:hover{ transform: translateY(-2px); }
    .profile-avatar-wrap img{ width:100%; height:100%; object-fit:cover; display:block; }

    /* Meta list */
    .meta-list li{ padding:.25rem 0; color:var(--muted); }
    .meta-list li strong{ color:#222; margin-right:.4rem; font-weight:600; }

    /* Tabs */
    .profile-tabs .nav-link {
      border-radius: .45rem;
      padding:.45rem .9rem;
      color:#1f2d3d;
      background: transparent;
      border: 1px solid transparent;
    }
    .profile-tabs .nav-link.active {
      background: linear-gradient(90deg, #ff8a3d, #ff6b21);
      color: #fff;
      box-shadow: 0 6px 18px rgba(255,107,33,.18);
      border-color: transparent;
    }

    /* Buttons - make Upload obvious */
    .btn-upload {
      background: linear-gradient(90deg,#ff8a3d,#ff6b21);
      color: #fff;
      border: none;
      box-shadow: 0 8px 20px rgba(255,107,33,.12);
      transition: transform .12s ease, box-shadow .18s;
    }
    .btn-upload:active { transform: translateY(1px) scale(.997); }
    .small-muted{ color:var(--muted); font-size:.9rem; }

    /* Responsive adjustments */
    @media (max-width: 991px){
      .profile-header{ height:120px; }
      .profile-avatar-wrap{ width:108px; height:108px; margin-top:-54px; border-width:5px; }
    }
  </style>

  <!-- Desktop tabs (top-right) -->
  <div class="d-none d-md-flex justify-content-end mb-3">
    <div class="btn-group profile-tabs" role="group" aria-label="Profile tabs">
      <button type="button" class="btn btn-outline-secondary btn-sm nav-link profile-tab-btn active" data-panel="info">User Information</button>
      <button type="button" class="btn btn-outline-secondary btn-sm nav-link profile-tab-btn" data-panel="security">Account Security</button>
    </div>
  </div>

  <div class="row g-4">
    <!-- LEFT: Profile card + details -->
    <div class="col-lg-5">
      <div class="card profile-card mb-3 overflow-hidden">
        <div class="profile-header"></div>

        <div class="card-body text-center">
      {{-- avatarForm: only used for uploading; Save button removed, auto-submit on file select --}}
      <form id="avatarForm" action="{{ route('beneficiary.profile.avatar') }}" method="POST" enctype="multipart/form-data" class="mb-3">
        @csrf
        <div class="mb-3 d-flex flex-column align-items-center">
            {{-- avatar is NOT a label anymore so clicking it will open the viewer modal --}}
            <div
                class="profile-avatar-wrap"
                id="avatarWrapper"
                style="cursor:pointer;"
                title="Click to view image"
                data-avatar="{{ !empty($beneficiary->avatar) ? asset('storage/' . $beneficiary->avatar) : '' }}"
            >
                 @if(!empty($beneficiary->avatar))
                     <img id="avatarPreview" src="{{ asset('storage/' . $beneficiary->avatar) }}" alt="avatar">
                 @else
                     <div id="avatarPlaceholder" class="d-flex align-items-center justify-content-center h-100 w-100 bg-secondary text-white fs-3">
                         {{ strtoupper(substr($beneficiary->first_name ?? $beneficiary->username,0,1)) }}
                     </div>
                     <img id="avatarPreview" src="" alt="avatar" style="display:none;">
                 @endif
             </div>

            <div class="mt-3 d-flex gap-2">
                {{-- Upload label still opens file selector --}}
                <label class="btn btn-upload btn-sm d-flex align-items-center gap-2" for="avatarInput">
                    <i class="fa fa-upload"></i> Upload Image
                </label>
                {{-- Save button removed per request; form will auto-submit when a file is chosen --}}
            </div>

            <div class="small-muted mt-2">Allow JPG / PNG • Max 2MB</div>
        </div>

        <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display:none;">
      </form>

      {{-- Reset avatar must remain and NOT be nested inside avatarForm --}}
      <form id="resetAvatarForm" action="{{ route('beneficiary.profile.avatar.reset') }}" method="POST" style="display:inline;margin-top:8px;">
          @csrf
          <button type="button" class="btn btn-outline-danger btn-sm" id="avatarResetBtn" data-bs-toggle="modal" data-bs-target="#confirmResetModal">
            Reset
          </button>
      </form>

          <div class="text-start mt-3">
            <ul class="list-unstyled meta-list mb-0">
              <li><strong>Username:</strong> {{ $beneficiary->username }}</li>
              <li><strong>Email:</strong> {{ $beneficiary->email }}</li>
              <li><strong>Type:</strong> {{ $beneficiaryType ?? '-' }}</li>
              <li><strong>Barangay:</strong> {{ $barangayName ?? '-' }}</li>
              <li><strong>Birthday:</strong> {{ $birthday ?? '-' }}</li>
              <li><strong>Age:</strong> {{ $age ?? '-' }}</li>
              <li><strong>Phone:</strong> {{ $phone ?? '-' }}</li>
              <li><strong>{{ $oscaPwdLabel ?? 'ID' }}:</strong> {{ $oscaPwdValue ?? '-' }}</li>
            </ul>
          </div>

          {{-- Save Image removed: uploads auto-submit on file select --}}
        </div>
      </div>
    </div>

    <!-- Mobile tabs (shown only on small screens) -> placed under the profile card so panels are below on mobile -->
    <div class="col-12 d-md-none mb-2">
      <div class="d-flex justify-content-center">
        <div class="btn-group profile-tabs-mobile" role="group" aria-label="Profile tabs mobile">
          <button type="button" class="btn btn-outline-secondary btn-sm profile-tab-btn" data-panel="info">Edit Profile</button>
          <button type="button" class="btn btn-outline-secondary btn-sm profile-tab-btn" data-panel="security">Account Security</button>
        </div>
      </div>
    </div>

    <!-- RIGHT: Toggleable panels -->
    <div class="col-lg-7">
      <div id="userInfoPanel" class="panel-card card mb-3 p-3">
        <div class="card-body">
          <h5 class="mb-3">Edit Profile</h5>
          <form id="updateProfileForm" action="{{ route('beneficiary.profile.update') }}" method="POST" enctype="multipart/form-data" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-6">
              <label class="form-label small-muted">First name</label>
              <input type="text" name="first_name" class="form-control form-control-sm" value="{{ old('first_name', $beneficiary->first_name) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small-muted">Last name</label>
              <input type="text" name="last_name" class="form-control form-control-sm" value="{{ old('last_name', $beneficiary->last_name) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small-muted">Phone</label>
              <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', $phone) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label small-muted">Birthday</label>
              <input type="date" name="birthday" class="form-control form-control-sm" value="{{ old('birthday', $beneficiary->birthday ? \Carbon\Carbon::parse($beneficiary->birthday)->format('Y-m-d') : '') }}">
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
              <button type="reset" class="btn btn-outline-secondary btn-sm">Reset</button>
              <button type="submit" class="btn btn-primary btn-sm">Save changes</button>
            </div>
          </form>
        </div>
      </div>

      <div id="accountSecurityPanel" class="panel-card card mb-3 p-3 d-none">
        <div class="card-body">
          <h5 class="mb-3">Account Security</h5>

          <form id="changePasswordForm" action="{{ route('beneficiary.profile.password') }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-12">
              <label class="form-label small-muted">Current Password</label>
              <input type="password" name="current_password" class="form-control form-control-sm" required>
            </div>

            <div class="col-md-6">
              <label class="form-label small-muted">New Password</label>
              <input type="password" name="password" id="newPassword" class="form-control form-control-sm" minlength="8" required>
            </div>

            <div class="col-md-6">
              <label class="form-label small-muted">Confirm New Password</label>
              <input type="password" name="password_confirmation" class="form-control form-control-sm" required>
            </div>

            <div class="col-12 d-flex gap-2 align-items-end">
              <div style="flex:1">
                <label class="form-label small-muted">Enter OTP</label>
                <input type="text" name="otp" id="otpInput" class="form-control form-control-sm" placeholder="Enter OTP">
              </div>
              <div>
                <button type="button" id="sendOtpBtn" class="btn btn-outline-dark btn-sm">Send OTP</button>
              </div>
            </div>

            <div class="col-12 d-flex justify-content-end">
              <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
            </div>
          </form>

          <div id="otpStatus" class="mt-2" style="display:none;"></div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

<!-- Avatar viewer modal -->
<div class="modal fade" id="avatarViewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center p-3">
        <img id="avatarViewImg" src="" alt="avatar" style="max-width:100%;height:auto;display:none;border-radius:8px;" />
        <div id="avatarViewLetter" style="width:140px;height:140px;border-radius:50%;background:#6c757d;color:#fff;display:none;align-items:center;justify-content:center;margin:0 auto;font-size:56px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Confirm reset modal -->
<div class="modal fade" id="confirmResetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content" style="border-radius:12px;overflow:hidden;">
      <div class="modal-body text-center p-4">
        <div style="width:56px;height:56px;margin:0 auto;border-radius:12px;background:#fff5f5;display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-exclamation-triangle" style="color:#e65151;font-size:22px;"></i>
        </div>
        <h5 class="mt-3 mb-1">Are you sure?</h5>
        <p class="text-muted mb-0">Resetting will restore the default profile image. This action cannot be undone.</p>
      </div>
      <div class="modal-footer d-flex flex-column gap-2 px-4 pb-4">
        <button type="button" id="confirmResetBtn" class="btn btn-danger w-100">Reset Profile Image</button>
        <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-hide flash messages after 3s
    const flash = document.querySelector('.alert');
    if (flash) {
      setTimeout(() => {
        flash.style.transition = 'opacity .4s ease';
        flash.style.opacity = '0';
        setTimeout(() => { flash.remove(); }, 500);
      }, 3000);
    }

    // Unified tab switching for desktop and mobile controls
    const panelInfo = document.getElementById('userInfoPanel');
    const panelSec  = document.getElementById('accountSecurityPanel');
    function showPanel(name){
      if(name === 'info'){
        panelInfo.classList.remove('d-none');
        panelSec.classList.add('d-none');
      } else {
        panelSec.classList.remove('d-none');
        panelInfo.classList.add('d-none');
      }
      // update all tab buttons' active state
      document.querySelectorAll('.profile-tab-btn').forEach(b => {
        if (b.dataset.panel === name) b.classList.add('active'); else b.classList.remove('active');
      });
    }
    // bind tab buttons (desktop + mobile)
    document.querySelectorAll('.profile-tab-btn').forEach(b => b.addEventListener('click', () => showPanel(b.dataset.panel)));
    // initial state
    showPanel('info');

    // Avatar interactions
    const input = document.getElementById('avatarInput');
    const preview = document.getElementById('avatarPreview');
    const placeholder = document.getElementById('avatarPlaceholder');
    const avatarWrapper = document.getElementById('avatarWrapper');
    const modalHtml = document.getElementById('avatarViewModal');
    const modalImg = document.getElementById('avatarViewImg');
    const modalLetter = document.getElementById('avatarViewLetter');

    // When file chosen: show preview and auto-submit the upload form
    if (input) {
      input.addEventListener('change', function (e) {
        const f = e.target.files[0];
        if (!f) {
          if (preview) preview.style.display = 'none';
          if (placeholder) placeholder.style.display = 'flex';
          return;
        }
        const reader = new FileReader();
        reader.onload = function (ev) {
          if (preview) {
            preview.src = ev.target.result;
            preview.style.display = 'block';
          }
          if (placeholder) placeholder.style.display = 'none';
          // auto-submit upload
          document.getElementById('avatarForm').submit();
        };
        reader.readAsDataURL(f);
      });
    }

    // Click avatar to view larger image (modal). Uses preview (new upload) or stored data-avatar.
    avatarWrapper?.addEventListener('click', function () {
      let src = '';
      if (preview && preview.getAttribute && preview.getAttribute('src')) {
        const s = preview.getAttribute('src') || '';
        if (s.trim() !== '') src = s;
      }
      if (!src && avatarWrapper && avatarWrapper.dataset) {
        src = (avatarWrapper.dataset.avatar || '').trim();
      }

      if (src) {
        modalImg.src = src;
        modalImg.style.display = 'block';
        modalLetter.style.display = 'none';
      } else {
        modalImg.style.display = 'none';
        modalLetter.style.display = 'flex';
        modalLetter.textContent = (placeholder?.textContent || 'U').trim();
      }

      if (modalHtml && typeof bootstrap !== 'undefined') {
        const bs = new bootstrap.Modal(modalHtml);
        bs.show();
      } else if (src) {
        window.open(src, '_blank');
      } else {
        alert('No profile image available to view.');
      }
    });

    // Reset via modal confirmation
    const resetForm = document.getElementById('resetAvatarForm');
    const confirmResetBtn = document.getElementById('confirmResetBtn');
    const confirmModalEl = document.getElementById('confirmResetModal');
    confirmResetBtn?.addEventListener('click', function () {
      if (resetForm) resetForm.submit();
      if (typeof bootstrap !== 'undefined' && confirmModalEl) {
        const bs = bootstrap.Modal.getInstance(confirmModalEl) || new bootstrap.Modal(confirmModalEl);
        bs.hide();
      }
    });

    // OTP placeholder (existing)
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const otpStatus = document.getElementById('otpStatus');
    sendOtpBtn?.addEventListener('click', function () {
      sendOtpBtn.disabled = true;
      otpStatus.style.display = 'block';
      otpStatus.className = 'alert alert-info';
      otpStatus.textContent = 'OTP sending is disabled (route not configured).';
      setTimeout(()=>{ sendOtpBtn.disabled = false; }, 900);
    });
  });
</script>
@endpush

