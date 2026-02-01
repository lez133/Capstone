<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authentication\LoginController;
use App\Http\Controllers\Authentication\CitizenRegistrationController;
use App\Http\Controllers\Admin\MswdDashboardController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\BeneficiaryDashViewer;
use App\Http\Controllers\Admin\SeniorCitizenBeneficiariesController;
use App\Http\Controllers\Admin\PWDBeneficiariesController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\BrgyRepresentative\BrgyRepDashboardController;
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
use App\Http\Controllers\Admin\DocumentManagementController;
use App\Http\Controllers\Admin\DistributionController;
use App\Http\Controllers\BrgyRepresentative\AssistRegistrationController;
use App\Http\Controllers\BrgyRepresentative\ViewScheduleController;
use App\Http\Controllers\BrgyRepresentative\TrackApplicationController;
use App\Http\Controllers\BrgyRepresentative\SubmitDocumentController;
use App\Http\Controllers\BrgyRepresentative\BrgyNotificationController;
use App\Http\Controllers\BrgyRepresentative\ViewRepProfileController;
use App\Http\Controllers\Admin\RegisteredPWDController;
use App\Http\Controllers\Beneficiary\DocumentController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\LogsController;
use App\Http\Controllers\Beneficiary\NotificationController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Authentication\ForgotPasswordController;
use Illuminate\Support\Facades\Crypt;

// -------------------- Home Route --------------------
Route::get('/', [LoginController::class, 'index'])->name('home');

// -------------------- Authentication Routes --------------------
Route::prefix('login')->group(function () {
    Route::get('/', [LoginController::class, 'index'])->name('login.index');
    Route::post('/', [LoginController::class, 'login'])->middleware('throttle:login')->name('login');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password reset routes
Route::get('forgot-password', [ForgotPasswordController::class, 'show'])->name('auth.password.request');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendReset'])->name('auth.password.request.post');

Route::get('reset-by-phone', [ForgotPasswordController::class, 'showPhoneResetForm'])->name('auth.password.reset.phone');
Route::post('reset-by-phone', [ForgotPasswordController::class, 'resetByPhone'])->name('auth.password.reset.phone.post');


// -------------------- MSWD Protected Routes --------------------
Route::middleware(['auth:mswd'])->group(function () {
    // -------------------- Dashboard Routes --------------------
    Route::get('/mswd', [MswdDashboardController::class, 'index'])->name('mswd.dashboard');

    // Dashboard stat card routes
    Route::get('/total-beneficiaries', [MswdDashboardController::class, 'totalBeneficiaries'])->name('total-beneficiaries');
    Route::get('/verified-beneficiaries', [MswdDashboardController::class, 'verifiedBeneficiaries'])->name('verified-beneficiaries');
    Route::get('/unverified-beneficiaries', [MswdDashboardController::class, 'unverifiedBeneficiaries'])->name('unverified-beneficiaries');
    Route::get('/admin/beneficiaries/export', [MswdDashboardController::class, 'export'])->name('beneficiaries.export');
    // -------------------- Member Management Routes --------------------
    Route::prefix('members')->group(function () {
        Route::get('/', [MemberController::class, 'index'])->name('members.index');
        Route::get('/create', [MemberController::class, 'create'])->name('members.create');
        Route::post('/store', [MemberController::class, 'store'])->name('members.store');
        Route::get('/show/{encryptedId}', [MemberController::class, 'show'])->name('members.show');
        Route::get('/edit/{encryptedId}', [MemberController::class, 'edit'])->name('members.edit');
        Route::put('/{encryptedId}', [MemberController::class, 'update'])->name('members.update');
        Route::get('/mswd', [MemberController::class, 'mswdMembers'])->name('members.mswd');
        Route::get('/brgy', [MemberController::class, 'brgyReps'])->name('members.brgy');
        Route::delete('/destroy/{encryptedId}', [MemberController::class, 'destroy'])->name('members.destroy');
    });
    Route::post('/admin/validate-member-field', [MemberController::class, 'validateField'])
        ->name('validate.member.field');

    // -------------------- Beneficiaries Management Routes --------------------
    Route::prefix('beneficiaries')->group(function () {
        //home
        Route::get('/', [BeneficiaryDashViewer::class, 'index'])->name('beneficiaries.index');
        Route::get('/beneficiaries/select-barangay', [BeneficiaryDashViewer::class, 'selectBarangay'])->name('beneficiaries.select-barangay');
        Route::get('/beneficiaries/interface/{encryptedBarangayId}', [BeneficiaryDashViewer::class, 'showBeneficiariesInterface'])->name('beneficiaries.interface');
        Route::get('/beneficiaries/barangay/search', [BeneficiaryDashViewer::class, 'search'])
        ->name('barangay.search');
        //Senior Citizens
        Route::get('/senior-citizen', [SeniorCitizenBeneficiariesController::class, 'seniorCitizenInterface'])->name('senior-citizen.interface');
        Route::get('/senior-citizen/search', [SeniorCitizenBeneficiariesController::class, 'searchBarangays'])->name('senior-citizen.search');
        Route::get('/senior-citizen/{encryptedBarangayId}/view', [SeniorCitizenBeneficiariesController::class, 'viewSeniorBeneficiaries'])->name('senior-citizen.view');
        Route::get('/add', [SeniorCitizenBeneficiariesController::class, 'create'])->name('beneficiaries.create');
        Route::post('/store', [SeniorCitizenBeneficiariesController::class, 'store'])->name('beneficiaries.store');
        Route::post('/import', [SeniorCitizenBeneficiariesController::class, 'import'])->name('beneficiaries.import');
        Route::get('/senior-citizen-beneficiaries/create', [SeniorCitizenBeneficiariesController::class, 'create'])->name('senior-citizen-beneficiaries.create');
        Route::get('senior-citizen-beneficiaries/{id}/edit', [SeniorCitizenBeneficiariesController::class, 'editSeniorBeneficiary'])->name('senior-citizen-beneficiaries.edit');
        Route::put('senior-citizen-beneficiaries/{id}', [SeniorCitizenBeneficiariesController::class, 'updateSeniorBeneficiary'])->name('senior-citizen-beneficiaries.update');
        Route::delete('senior-citizen-beneficiaries/{id}', [SeniorCitizenBeneficiariesController::class, 'deleteSeniorBeneficiary'])->name('senior-citizen-beneficiaries.delete');
        Route::get('senior-citizen-beneficiaries/export/{encryptedBarangayId}', [SeniorCitizenBeneficiariesController::class, 'exportSeniorBeneficiaries'])->name('senior-citizen-beneficiaries.export');
        Route::get('senior-citizen/download-template', [SeniorCitizenBeneficiariesController::class, 'downloadTemplate'])->name('senior-citizen.download-template');
        Route::post('/senior/undo-import', [SeniorCitizenBeneficiariesController::class, 'undoImport'])->name('senior.undoImport');
        //pwds
        // JSON endpoint for edit modal
        Route::get('/pwd/{id}/json', [PWDBeneficiariesController::class, 'json'])->name('pwd.json');
         Route::get('/pwd', [PWDBeneficiariesController::class, 'SelectBrgyInterface'])->name('pwd.interface');
         Route::get('/pwd/{encryptedBarangayId}/view', [PWDBeneficiariesController::class, 'viewPWDBeneficiaries'])->name('pwd.view');
         Route::get('/pwd/search', [PWDBeneficiariesController::class, 'searchBarangays'])->name('pwd.search');
         Route::get('/pwd/add', [PWDBeneficiariesController::class, 'create'])->name('pwd.create');
         Route::post('/pwd/store', [PWDBeneficiariesController::class, 'store'])->name('pwd.store');
         Route::post('/pwd/import-csv', [PWDBeneficiariesController::class, 'importCsv'])->name('pwd.importCsv');
         Route::get('/pwd/download-template', [PWDBeneficiariesController::class, 'downloadTemplate'])->name('pwd.download-template');

        // Edit / Update / Delete using PWDBeneficiariesController (for view-pwds links)
        Route::put('/pwd/{id}', [PWDBeneficiariesController::class, 'update'])->name('pwd.update');
        Route::delete('/pwd/{id}', [PWDBeneficiariesController::class, 'destroy'])->name('pwd.delete');
        Route::post('/pwd/undo-import', [PWDBeneficiariesController::class, 'undoImport'])->name('pwd.undoImport');
    });

    // -------------------- Schedule Management Routes --------------------
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::get('schedules/calendar', [ScheduleController::class, 'calendar'])
    ->name('schedules.calendar');
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
            Route::post('/programs/types/add', [ProgramTypeController::class, 'store'])->name('program-types.store');
            Route::put('/{id}', [ProgramTypeController::class, 'update'])->name('program-types.update');
            Route::delete('/{id}', [ProgramTypeController::class, 'destroy'])->name('program-types.destroy');
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
    Route::get('/senior-citizen-beneficiaries/{encryptedBarangayId}', [SeniorCitizenBeneficiariesController::class, 'viewSeniorBeneficiaries'])
        ->name('senior-citizen-beneficiaries.view');
    Route::get('/senior-citizen-beneficiaries', [SeniorCitizenBeneficiariesController::class, 'index'])
        ->name('senior-citizen-beneficiaries.index');

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
        Route::delete('/delete/{id}', [RegisteredSeniorCitizenController::class, 'deleteBeneficiary'])->name('senior-citizens.delete');
        Route::get('export/{encryptedBarangayId}', [RegisteredSeniorCitizenController::class, 'exportCsv'])
            ->name('senior-citizens.export');
        Route::get('/senior/related-search', [SeniorCitizenBeneficiariesController::class, 'relatedSearch'])->name('senior-citizens.related-search');
    });

    // -------------------- Admin Document Management Routes --------------------
    Route::prefix('admin')->group(function () {
        Route::get('/documents/selector', [DocumentManagementController::class, 'selector'])->name('admin.documents.selector');
        Route::get('/documents/beneficiary-program-documents', [DocumentManagementController::class, 'beneficiaryProgramDocuments'])
            ->name('document.beneficiary.program.documents');
        Route::get('/documents/beneficiaries/{type}', [DocumentManagementController::class, 'registeredBeneficiariesDocuments'])
            ->name('admin.documents.beneficiaries.documents');
        Route::get('/documents/manage/{beneficiaryId}', [DocumentManagementController::class, 'manageRegisteredDocument'])
            ->name('admin.documents.manage');
        Route::get('/documents/view/{id}', [DocumentManagementController::class, 'viewPdf'])->name('admin.documents.view');
        Route::get('/documents/download/{id}', [DocumentManagementController::class, 'download'])->name('admin.documents.download');
        Route::post('/documents/verify/{id}', [DocumentManagementController::class, 'verify'])->name('admin.documents.verify');
        Route::post('/documents/disable/{id}', [DocumentManagementController::class, 'disable'])->name('admin.documents.disable');
        Route::post('/documents/enable/{id}', [DocumentManagementController::class, 'enable'])->name('admin.documents.enable');
        Route::post('/documents/reject/{id}', [DocumentManagementController::class, 'reject'])->name('admin.documents.reject');
        Route::post('/documents/reverify/{id}', [DocumentManagementController::class, 'reverify'])->name('admin.documents.reverify');
        Route::get('/documents/barangay-search', [DocumentManagementController::class, 'documentBarangaySelector'])
            ->name('document.barangay.selector');
        Route::get('/documents/program-type-selector',[DocumentManagementController::class, 'programTypeSelector'])
            ->name('document.program.type.selector');
        Route::get('/admin/documents/registered/{beneficiary}', [DocumentManagementController::class, 'showRegisteredDocument'])
            ->name('document.manage-registered-document');
    });


    Route::prefix('notifications')->group(function () {
        Route::get('/', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/sms', [AdminNotificationController::class, 'sendSms'])->name('notifications.sms');
        Route::post('/gmail', [AdminNotificationController::class, 'sendGmail'])->name('notifications.gmail');
        Route::post('/notice', [AdminNotificationController::class, 'sendNotice'])->name('notifications.notice');
        Route::get('/history', [AdminNotificationController::class, 'history'])->name('notifications.history');
    });

    // -------------------- Distribution Management Routes --------------------
    Route::prefix('admin/distribution')->group(function () {
        Route::get('/barangays/{schedule?}', [DistributionController::class, 'BrgySelection'])->name('distribution.barangays');
        Route::get('/category', [DistributionController::class, 'category'])->name('distribution.category');
        Route::get('/schedules/{status}', [DistributionController::class, 'schedules'])->name('distribution.schedules');
        Route::get('/beneficiaries/{schedule}/{barangay}', [DistributionController::class, 'beneficiaries'])->name('distribution.beneficiaries');
        Route::post('/mark-received', [DistributionController::class, 'markReceived'])->name('distribution.markReceived');
        Route::get('distribution/{scheduleId}/{barangayId}/beneficiaries/export', [DistributionController::class, 'exportBeneficiariesCsv'])
        ->name('distribution.beneficiaries.export');
    });
    Route::get('/admin/distribution', [DistributionController::class, 'selectBarangay'])
        ->name('distribution.index');

    // -------------------- PWD Management Routes --------------------
    Route::prefix('pwd')->group(function () {
        Route::get('/select-barangay', [RegisteredPWDController::class, 'selectBarangay'])
            ->name('pwd.select-barangay');
        Route::get('/manage/{encryptedBarangayId}', [RegisteredPWDController::class, 'managePWDs'])
            ->name('pwd.manage');
        Route::get('/verified/{encryptedBarangayId}', [RegisteredPWDController::class, 'verifiedBeneficiaries'])
            ->name('pwd.verified');
        Route::get('/not-verified/{encryptedBarangayId}', [RegisteredPWDController::class, 'notVerifiedBeneficiaries'])
            ->name('pwd.not-verified');
        Route::post('/verify/{id}', [RegisteredPWDController::class, 'verifyBeneficiary'])
            ->name('pwd.verify');
        Route::post('/disable/{id}', [RegisteredPWDController::class, 'disableBeneficiary'])
            ->name('pwd.disable');
        Route::post('/delete/{id}', [RegisteredPWDController::class, 'deleteBeneficiary'])
            ->name('pwd.delete');
        Route::get('/related-search', [PWDBeneficiariesController::class, 'relatedSearch'])
            ->name('pwd.related-search');
    });

    // Settings
    Route::get('settings', [AdminSettingsController::class, 'index'])->name('admin.settings.index');
    // Profile Settings (Change Password)
    Route::get('/settings/profile', [AdminSettingsController::class, 'index'])
        ->name('profile.settings');
    Route::post('/settings/profile/password', [AdminSettingsController::class, 'updatePassword'])
        ->name('profile.password.update');

    // Reports / Activity logs
    Route::get('reports', [LogsController::class, 'index'])->name('reports.index');
    Route::get('reports/data', [LogsController::class, 'data'])->name('admin.reports.data');
    Route::get('reports/export/csv', [LogsController::class, 'exportCsv'])->name('admin.reports.export.csv');
    Route::get('reports/export/pdf', [LogsController::class, 'exportPdf'])->name('admin.reports.export.pdf');

    // Programs coverage report (overview UI + JSON data)
    Route::get('reports/overview', [ReportsController::class, 'overview'])->name('admin.reports.overview');
    Route::get('reports/coverage-data', [ReportsController::class, 'data'])->name('admin.reports.coverage.data');
});

// -------------------- Barangay Rep Protected Routes --------------------
Route::middleware(['auth:brgyrep'])->group(function () {
    // -------------------- Dashboard Routes --------------------
    Route::get('/brgyrep', [BrgyRepDashboardController::class, 'index'])->name('brgyrep.dashboard');

    // -------------------- Assist Registration Routes --------------------
    Route::get('/assist-registration', [AssistRegistrationController::class, 'create'])->name('assist-registration.create');
    Route::post('/assist-registration', [AssistRegistrationController::class, 'store'])->name('assist-registration.store');
    Route::post('/assist-registration/validate-field', [AssistRegistrationController::class, 'validateField'])
        ->name('assist-registration.validate-field');

    Route::get('/brgyrep/view-schedules', [ViewScheduleController::class, 'index'])
        ->name('brgyrep.view-schedules');

    // -------------------- Track Applications Routes --------------------
    Route::get('/brgyrep/track-applications', [TrackApplicationController::class, 'index'])
        ->name('brgyrep.track-applications.index');
    Route::get('/brgyrep/track-applications/{aidProgram}/{beneficiary}', [TrackApplicationController::class, 'show'])
        ->name('brgyrep.track-applications.show');
    Route::get('/brgyrep/track-applications/document/{document}/download', [TrackApplicationController::class, 'download'])
        ->name('brgyrep.track-applications.download');
    Route::post('/brgyrep/track-applications/{aidProgram}/{beneficiary}/{requirement}/review', [TrackApplicationController::class, 'review'])
        ->name('brgyrep.track-applications.review');
    // -------------------- Submit Document Routes --------------------
    Route::prefix('submit-document')->name('brgyrep.submit-document.')->group(function () {
        Route::get('/', [SubmitDocumentController::class, 'create'])->name('create');
        Route::post('/', [SubmitDocumentController::class, 'store'])->name('store');
        Route::get('/{id}', [SubmitDocumentController::class, 'show'])->name('show');
    });
    Route::get('/brgyrep/requirements', [SubmitDocumentController::class, 'getRequirements'])
        ->name('brgyrep.requirements');

    // View all documents submitted by a beneficiary
    Route::get('/brgyrep/beneficiary/{id}/documents', [SubmitDocumentController::class, 'viewSubmittedDocuments'])
        ->name('brgyrep.beneficiary.documents');
    Route::get('/brgyrep/documents/{docId}/view', [SubmitDocumentController::class, 'viewDocument'])
        ->name('brgyrep.documents.view');
    Route::post('/brgyrep/documents/{id}/review', [SubmitDocumentController::class, 'markAsReviewed'])
        ->name('brgyrep.documents.review');

    // -------------------- Barangay Representative Notification Routes --------------------
    Route::get('/brgyrep/notifications/send', [BrgyNotificationController::class, 'send'])
        ->name('brgyrep.notifications.send');
    Route::post('/brgyrep/notifications/sms', [BrgyNotificationController::class, 'sendSms'])
        ->name('brgyrep.notifications.sendSms');
    Route::post('/brgyrep/notifications/email', [BrgyNotificationController::class, 'sendEmail'])
        ->name('brgyrep.notifications.sendEmail');
    Route::get('/brgyrep/notifications/view', [BrgyNotificationController::class, 'view'])
        ->name('brgyrep.notifications.view');
    Route::get('/brgyrep/notifications', [BrgyNotificationController::class, 'interface'])
        ->name('brgyrep.notifications.interface');
    // View Profile for Barangay Representative
    Route::get('/brgyrep/profile/{encryptedId}', [ViewRepProfileController::class, 'show'])
        ->name('brgyrep.profile.view');

    // Edit Barangay Representative Profile
    Route::get('/brgyrep/profile/{encryptedId}/edit', [ViewRepProfileController::class, 'edit'])
        ->name('representatives.edit');
    Route::put('/brgyrep/profile/{encryptedId}/update', [ViewRepProfileController::class, 'update'])
        ->name('representatives.update');

    // List of Representatives (for Back button)
    Route::get('/brgyrep/representatives', [ViewRepProfileController::class, 'index'])
        ->name('representatives.index');

    // Change Password Page (GET, encrypted id)
    Route::get('/brgyrep/profile/{encryptedId}/password', [ViewRepProfileController::class, 'passwordSettings'])
        ->name('brgyrep.password.settings');

    // Change Password Action (POST)
    Route::post('/brgyrep/profile/password/update', [ViewRepProfileController::class, 'updatePassword'])
        ->name('brgyrep.password.update');
});

// -------------------- Citizen Registration Routes (public) --------------------
Route::get('/register-as-citizen', [CitizenRegistrationController::class, 'create'])->name('register-as-citizen');
Route::post('/register-as-citizen', [CitizenRegistrationController::class, 'store'])->name('register-as-citizen.store');
Route::post('/validate-citizen-field', [CitizenRegistrationController::class, 'validateField'])->name('validate.field');
Route::post('/send-otp', [CitizenRegistrationController::class, 'sendOtp'])->name('send.otp');
Route::post('/validate-otp', [CitizenRegistrationController::class, 'validateOtp'])->name('validate.otp');

// -------------------- Beneficiary Dashboard Routes --------------------
Route::middleware(['auth:beneficiary'])->group(function () {
    // --- Notifications for beneficiaries ---
    Route::prefix('beneficiary')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('beneficiary.notifications.index');
        Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])
            ->name('beneficiary.notifications.markRead');
        Route::post('/notifications/{id}/{action}', [NotificationController::class, 'mark'])
            ->name('beneficiary.notifications.mark');
    });

    //dashboard
    Route::get('/beneficiaries/dashboard', [BeneficiaryDashboardController::class, 'index'])
        ->name('beneficiaries.dashboard');

    //profile
    Route::get('/beneficiaries/profile', [ProfileController::class, 'index'])
        ->name('beneficiaries.profile');
    Route::put('/beneficiary/profile', [ProfileController::class, 'update'])
        ->name('beneficiary.profile.update');

    // avatar upload/reset/password
    Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar'])
        ->name('beneficiary.profile.avatar');
    Route::post('profile/avatar/reset', [ProfileController::class, 'resetAvatar'])
        ->name('beneficiary.profile.avatar.reset');

    Route::put('profile/password', [ProfileController::class, 'updatePassword'])
        ->name('beneficiary.profile.password');
    //aid applications
    Route::get('/beneficiaries/applications', [AidApplicationController::class, 'index'])
        ->name('beneficiaries.applications');
    Route::get('/beneficiaries/apply/{id}', [AidApplicationController::class, 'apply'])
        ->name('beneficiaries.apply');

    // Confirm received (mark program requirements as received for the beneficiary)
    Route::post('/beneficiaries/application/{aidProgram}/confirm-received', [AidApplicationController::class, 'confirmReceived'])
        ->name('beneficiaries.application.confirm_received');

    Route::get('/beneficiary/aid-application/{aidProgramId}/requirement/{requirementId}/submit-document',
        [AidApplicationController::class, 'showSubmitDocumentForm'])
        ->name('beneficiary.submit-document.form');
    Route::post('/beneficiary/aid-application/{aidProgramId}/requirement/{requirementId}/submit-document', [AidApplicationController::class, 'storeSubmittedDocument'])
        ->name('beneficiary.submit-document.store');
    Route::delete('/beneficiary/aid-application/{aidProgramId}/requirement/{requirementId}/retract-document',[AidApplicationController::class, 'retractDocument'])
        ->name('beneficiary.retract-document');
    //beneficiary documents
    Route::get('/beneficiaries/documents', [DocumentController::class, 'index'])
        ->name('beneficiaries.documents');
    Route::post('/beneficiaries/documents/submit', [DocumentController::class, 'submit'])
        ->name('beneficiaries.documents.submit');
    Route::get('/beneficiaries/documents/download/{id}', [DocumentController::class, 'download'])
        ->name('beneficiaries.documents.download');
    Route::get('beneficiaries/documents/{id}/view', [DocumentController::class, 'view'])
        ->name('beneficiaries.documents.view');
    // delete (retract) a beneficiary document - protected by the same middleware group
    Route::delete('/beneficiaries/documents/{id}', [DocumentController::class, 'destroy'])
        ->name('beneficiaries.documents.destroy');
});

// -------------------- Centenarian Cash Gift Route --------------------
Route::middleware(['auth:beneficiary', 'verified'])->prefix('beneficiary')->group(function () {
    Route::get('/centenarian-cash-gift', [\App\Http\Controllers\Beneficiary\CentenarianCashGiftController::class, 'index'])->name('beneficiary.centenarian-cash-gift');
});



















