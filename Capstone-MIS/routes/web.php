<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authentication\LoginController;
use App\Http\Controllers\authentication\LoginGoogleController;
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

Route::get('/', [LoginController::class, 'index'])->name('home');

// Login routes
Route::get('/login', [LoginController::class, 'index'])->name('login.index');
Route::post('/login', [LoginController::class, 'login'])->name('login');
// Logout route
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
// Google login routes
Route::get('/login/google', [LoginGoogleController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/login/google/callback', [LoginGoogleController::class, 'handleGoogleCallback']);
// dashboard route
Route::get('/mswd', [MswdController::class, 'index'])->name('mswd.dashboard')->middleware('auth');
Route::get('/brgyrep', [BrgyRepController::class, 'index'])->name('brgyrep.dashboard')->middleware('auth');

// Notification routes
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/sms', [NotificationController::class, 'sendSms'])->name('notifications.sms');
Route::post('/notifications/gmail', [NotificationController::class, 'sendGmail'])->name('notifications.gmail');
Route::post('/notifications/notice', [NotificationController::class, 'sendNotice'])->name('notifications.notice'); // Add this route

// Member management routes
Route::get('/members', [MemberController::class, 'index'])->name('members.index'); // List members
Route::get('/members/create', [MemberController::class, 'create'])->name('members.create'); // Add member form
Route::post('/members/store', [MemberController::class, 'store'])->name('members.store'); // Store member data
Route::get('/members/show/{encryptedId}', [MemberController::class, 'show'])->name('members.show');
Route::get('/members/edit/{encryptedId}', [MemberController::class, 'edit'])->name('members.edit');
Route::put('/members/{id}', [MemberController::class, 'update'])->name('members.update');
Route::get('/members/mswd', [MemberController::class, 'mswdMembers'])->name('members.mswd');
Route::get('/members/brgy', [MemberController::class, 'brgyReps'])->name('members.brgy');
Route::post('/validate-member-field', [MemberController::class, 'validateField'])->name('members.validateField');

// Beneficiaries management routes
Route::get('/beneficiaries', [BeneficiariesController::class, 'index'])->name('beneficiaries.index');
Route::get('/beneficiaries/seniors', [BeneficiariesController::class, 'seniors'])->name('beneficiaries.seniors');
Route::get('/beneficiaries/pwds', [BeneficiariesController::class, 'pwds'])->name('beneficiaries.pwds');

// Schedule management routes
Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');

// Program management routes
Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');

Route::prefix('admin/programs/barangays')->group(function () {
    Route::get('/', [BarangayController::class, 'index'])->name('barangays.index');
    Route::post('/add', [BarangayController::class, 'store'])->name('barangays.store');
    Route::post('/import', [BarangayController::class, 'import'])->name('barangays.import');
    Route::get('/export', [BarangayController::class, 'export'])->name('barangays.export');
});

Route::prefix('admin/programs/types')->group(function () {
    Route::get('/', [ProgramTypeController::class, 'index'])->name('program-types.index');
    Route::post('/add', [ProgramTypeController::class, 'store'])->name('program-types.store');
});

Route::prefix('admin/programs/aid')->group(function () {
    Route::get('/', [AidProgramController::class, 'index'])->name('aid-programs.index');
    Route::post('/add', [AidProgramController::class, 'store'])->name('aid-programs.store');
    Route::get('/{id}', [AidProgramController::class, 'show'])->name('aid-programs.show');
    Route::put('/{id}', [AidProgramController::class, 'update'])->name('aid-programs.update');
    Route::delete('/{id}', [AidProgramController::class, 'destroy'])->name('aid-programs.destroy');
});
