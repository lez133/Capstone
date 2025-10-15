<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authentication\LoginController;
use App\Http\Controllers\Authentication\LoginGoogleController;
use App\Http\Controllers\Authentication\CitizenRegistrationController;
use App\Http\Controllers\Admin\MswdController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\BeneficiariesController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\BrgyRepresentative\BrgyRepController;
use App\Http\Controllers\Admin\BarangayController;
use App\Http\Controllers\Admin\ProgramTypeController;
use App\Http\Controllers\Admin\AidProgramController;
use App\Http\Controllers\Profiles\ViewProfileController;
use App\Http\Controllers\Beneficiary\BeneficiaryDashboardController;
use App\Http\Controllers\Beneficiary\ProfileController;
use App\Http\Controllers\Admin\RegisteredSeniorCitizenController;
use App\Http\Controllers\Admin\RequirementController;
use App\Http\Controllers\Beneficiary\BeneficiaryOtpController;
use App\Http\Controllers\Beneficiary\AidApplicationController;

// -------------------- Home Route --------------------
Route::get('/', [LoginController::class, 'index'])->name('home');

// -------------------- Authentication Routes --------------------
Route::prefix('login')->group(function () {
    Route::get('/', [LoginController::class, 'index'])->name('login.index');
    Route::post('/', [LoginController::class, 'login'])->middleware('throttle:login')->name('login');
    Route::get('/google', [LoginGoogleController::class, 'redirectToGoogle'])->name('login.google');
    Route::get('/google/callback', [LoginGoogleController::class, 'handleGoogleCallback']);
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// -------------------- Dashboard Routes --------------------
Route::middleware('auth')->group(function () {
    Route::get('/mswd', [MswdController::class, 'index'])->name('mswd.dashboard');
    Route::get('/brgyrep', [BrgyRepController::class, 'index'])->name('brgyrep.dashboard');
});

// -------------------- Notification Routes --------------------
Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/sms', [NotificationController::class, 'sendSms'])->name('notifications.sms');
    Route::post('/gmail', [NotificationController::class, 'sendGmail'])->name('notifications.gmail');
    Route::post('/notice', [NotificationController::class, 'sendNotice'])->name('notifications.notice');
});

// -------------------- Member Management Routes --------------------
Route::prefix('members')->group(function () {
    Route::get('/', [MemberController::class, 'index'])->name('members.index');
    Route::get('/create', [MemberController::class, 'create'])->name('members.create');
    Route::post('/store', [MemberController::class, 'store'])->name('members.store');
    Route::get('/show/{encryptedId}', [MemberController::class, 'show'])->name('members.show');
    Route::get('/edit/{encryptedId}', [MemberController::class, 'edit'])->name('members.edit');
    Route::put('/{id}', [MemberController::class, 'update'])->name('members.update');
    Route::get('/mswd', [MemberController::class, 'mswdMembers'])->name('members.mswd');
    Route::get('/brgy', [MemberController::class, 'brgyReps'])->name('members.brgy');
});
Route::post('/admin/validate-member-field', [MemberController::class, 'validateField'])
    ->name('validate.member.field');

// -------------------- Beneficiaries Management Routes --------------------
Route::prefix('beneficiaries')->group(function () {
    Route::get('/', [BeneficiariesController::class, 'index'])->name('beneficiaries.index');
    Route::get('/seniors', [BeneficiariesController::class, 'seniors'])->name('beneficiaries.seniors');
    Route::get('/pwds', [BeneficiariesController::class, 'pwds'])->name('beneficiaries.pwds');
    Route::get('/senior-citizen', [BeneficiariesController::class, 'seniorCitizenInterface'])->name('senior-citizen.interface');
    Route::get('/senior-citizen/search', [BeneficiariesController::class, 'searchBarangays'])->name('senior-citizen.search');
    Route::get('/senior-citizen/{barangay}/view', [BeneficiariesController::class, 'viewSeniorBeneficiaries'])->name('senior-citizen.view');
    Route::get('/add', [BeneficiariesController::class, 'create'])->name('beneficiaries.create');
    Route::post('/store', [BeneficiariesController::class, 'store'])->name('beneficiaries.store');
    Route::post('/import', [BeneficiariesController::class, 'import'])->name('beneficiaries.import');
    Route::get('/senior-citizen-beneficiaries/create', [BeneficiariesController::class, 'create'])->name('senior-citizen-beneficiaries.create');
});

// -------------------- Schedule Management Routes --------------------
Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
Route::resource('schedules', ScheduleController::class);
Route::post('schedules/{schedule}/publish', [ScheduleController::class, 'publish'])->name('schedules.publish');
Route::post('schedules/{schedule}/publish-notify', [ScheduleController::class, 'publishNotify'])->name('schedules.publishNotify');
Route::post('schedules/{schedule}/unpublish', [ScheduleController::class, 'unpublish'])->name('schedules.unpublish');
Route::post('schedules/{schedule}/unpublish-notify', [ScheduleController::class, 'unpublishNotify'])->name('schedules.unpublishNotify');
// -------------------- Program Management Routes --------------------
Route::prefix('programs')->group(function () {
    Route::get('/', [ProgramController::class, 'index'])->name('programs.index');

    Route::prefix('barangays')->group(function () {
        Route::get('/', [BarangayController::class, 'index'])->name('barangays.index');
        Route::post('/add', [BarangayController::class, 'store'])->name('barangays.store');
        Route::post('/import', [BarangayController::class, 'import'])->name('barangays.import');
        Route::get('/export', [BarangayController::class, 'export'])->name('barangays.export');
        Route::put('/update/{id}', [BarangayController::class, 'update'])->name('barangays.update');
        Route::delete('/delete/{id}', [BarangayController::class, 'destroy'])->name('barangays.destroy');
        Route::get('/create', [BarangayController::class, 'create'])->name('barangays.create');
    });

    Route::prefix('types')->group(function () {
        Route::get('/', [ProgramTypeController::class, 'index'])->name('program-types.index');
        Route::post('/add', [ProgramTypeController::class, 'store'])->name('program-types.store');
    });

    Route::prefix('aid')->group(function () {
        Route::get('/', [AidProgramController::class, 'index'])->name('aid-programs.index');
        Route::post('/add', [AidProgramController::class, 'store'])->name('aid-programs.store');
        Route::get('/{id}', [AidProgramController::class, 'show'])->name('aid-programs.show');
        Route::put('/{id}', [AidProgramController::class, 'update'])->name('aid-programs.update');
        Route::delete('/{id}', [AidProgramController::class, 'destroy'])->name('aid-programs.destroy');
        Route::post('/requirements', [AidProgramController::class, 'storeRequirement'])
        ->name('aid.requirements.store');
    });

    Route::resource('requirements', RequirementController::class)
        ->except(['show', 'edit', 'create'])
        ->names([
            'index'   => 'requirements.index',
            'store'   => 'requirements.store',
            'destroy' => 'requirements.destroy',
            'update'  => 'requirements.update',
        ]);
});

// -------------------- View Profile Route --------------------
Route::get('/view-profile/{id}', [ViewProfileController::class, 'show'])->name('view-profile.show');

// -------------------- Senior Citizen Beneficiaries Routes --------------------
Route::get('/senior-citizen-beneficiaries/{encryptedBarangayId}', [BeneficiariesController::class, 'viewSeniorBeneficiaries'])
    ->name('senior-citizen-beneficiaries.view');
Route::get('/senior-citizen-beneficiaries', [BeneficiariesController::class, 'index'])
    ->name('senior-citizen-beneficiaries.index');

// -------------------- Citizen Registration Routes --------------------
Route::get('/register-as-citizen', [CitizenRegistrationController::class, 'create'])->name('register-as-citizen');
Route::post('/register-as-citizen', [CitizenRegistrationController::class, 'store'])->name('register-as-citizen.store');
Route::post('/validate-citizen-field', [CitizenRegistrationController::class, 'validateField'])->name('validate.field');
//otp
Route::middleware(['auth:beneficiary'])->group(function () {
    Route::get('/beneficiary/otp', [BeneficiaryOtpController::class, 'show'])
        ->name('beneficiary.otp');
    Route::post('/beneficiary/otp', [BeneficiaryOtpController::class, 'verify'])
        ->name('beneficiary.otp.verify');
    Route::post('/beneficiary/otp/resend', [BeneficiaryOtpController::class, 'resend'])->name('beneficiary.otp.resend');
});
// -------------------- Beneficiary Dashboard Routes --------------------
Route::middleware(['auth:beneficiary', 'beneficiary.otp'])->group(function () {
    Route::get('/beneficiaries/dashboard', [BeneficiaryDashboardController::class, 'index'])
        ->name('beneficiaries.dashboard');
    Route::get('/beneficiaries/profile', [ProfileController::class, 'index'])
        ->name('beneficiaries.profile');
    Route::put('/beneficiary/profile', [ProfileController::class, 'update'])
        ->name('beneficiary.profile.update');
    Route::get('/beneficiaries/applications', [AidApplicationController::class, 'index'])
        ->name('beneficiaries.applications');
    Route::get('/beneficiaries/apply/{id}', [AidApplicationController::class, 'apply'])
        ->name('beneficiaries.apply');
    Route::get('/beneficiaries/documents', [\App\Http\Controllers\Beneficiary\DocumentController::class, 'index'])
        ->name('beneficiaries.documents');
    Route::post('/beneficiaries/documents/submit', [\App\Http\Controllers\Beneficiary\DocumentController::class, 'submit'])
        ->name('beneficiaries.documents.submit');
    Route::get('/beneficiaries/documents/download/{id}', [\App\Http\Controllers\Beneficiary\DocumentController::class, 'download'])
        ->name('beneficiaries.documents.download');
});

// -------------------- Senior Citizens Management Routes --------------------
Route::prefix('senior-citizens')->group(function () {
    Route::get('/select-barangay', [RegisteredSeniorCitizenController::class, 'selectBarangay'])
        ->name('senior-citizens.select-barangay');
    Route::get('/manage/{encryptedBarangayId}', [RegisteredSeniorCitizenController::class, 'manageSeniorCitizens'])
        ->name('senior-citizens.manage');
    Route::get('/verified/{encryptedBarangayId}', [RegisteredSeniorCitizenController::class, 'verifiedBeneficiaries'])
        ->name('senior-citizens.verified');
    Route::get('/not-verified/{encryptedBarangayId}', [RegisteredSeniorCitizenController::class, 'notVerifiedBeneficiaries'])
        ->name('senior-citizens.not-verified');
    Route::post('/verify/{id}', [RegisteredSeniorCitizenController::class, 'verifyBeneficiary'])
        ->name('senior-citizens.verify');
    Route::post('/disable/{id}', [RegisteredSeniorCitizenController::class, 'disableBeneficiary'])
        ->name('senior-citizens.disable');
    Route::post('/edit/{id}', [RegisteredSeniorCitizenController::class, 'editBeneficiary'])
        ->name('senior-citizens.edit');
    Route::post('/delete/{id}', [RegisteredSeniorCitizenController::class, 'deleteBeneficiary'])
        ->name('senior-citizens.delete');
});














