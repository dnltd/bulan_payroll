<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\VerifyController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\PayslipController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DeductionController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\RoundTripController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Dispatcher\DispatcherController;

// Default route redirects to login
Route::get('/', fn () => redirect()->route('login'));

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// OTP Routes
Route::get('/otp', [LoginController::class, 'showOtpForm'])->name('otp.form');
Route::post('/otp', [LoginController::class, 'verifyOtp'])->name('otp.verify');
Route::get('/otp/resend', [LoginController::class, 'resendOtp'])->name('otp.resend');

// Forgot Password 
Route::get('/forgot-password', [VerifyController::class, 'showEmailForm'])->name('password.request');
Route::post('/forgot-password/send', [VerifyController::class, 'sendOTP'])->name('password.send');
Route::get('/verify-otp', [VerifyController::class, 'showOTPForm'])->name('password.otp.verify');
Route::post('/verify-otp', [VerifyController::class, 'verifyOTP'])->name('password.otp.check');
Route::get('/create-new-password', [VerifyController::class, 'showCreatePassword'])->name('password.create');
Route::post('/create-new-password', [VerifyController::class, 'saveNewPassword'])->name('password.store');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Payroll
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payslip/view/{id}', [PayslipController::class, 'view'])->name('payslip.view');
    Route::get('/payslip/print/{id}', [PayslipController::class, 'print'])->name('payslip.print');
    Route::get('/payslip/download/{id}', [PayslipController::class, 'download'])->name('payslip.download');
    Route::get('/payslips/bulk-download', [PayslipController::class, 'bulkDownload'])->name('payslips.bulk');

    // Employees
Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
Route::post('/employees/store', [EmployeeController::class, 'store'])->name('employees.store');
Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    // Deductions
Route::get('/deductions', [DeductionController::class, 'index'])->name('deductions.index');
Route::get('/deductions/create', [DeductionController::class, 'create'])->name('deductions.create');
Route::post('/deductions/store', [DeductionController::class, 'store'])->name('deductions.store');
Route::get('/deductions/edit/{id}', [DeductionController::class, 'edit'])->name('deductions.edit');
Route::put('/deductions/update/{id}', [DeductionController::class, 'update'])->name('deductions.update');
Route::delete('/deductions/delete/{id}', [DeductionController::class, 'destroy'])->name('deductions.destroy');
Route::get('deductions/export/pdf', [DeductionController::class, 'exportPDF'])->name('deductions.export.pdf');
Route::get('deductions/print', [DeductionController::class, 'print'])->name('deductions.print');

    // Holidays 
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
    Route::get('/holidays/create', [HolidayController::class, 'create'])->name('holidays.create');
    Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
    Route::get('/holidays/{holiday}/edit', [HolidayController::class, 'edit'])->name('holidays.edit');
    Route::put('/holidays/{holiday}', [HolidayController::class, 'update'])->name('holidays.update');
    Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');

    // Attendance
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::get('/attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');
Route::post('/attendance/capture', [AttendanceController::class, 'capture'])->name('attendance.capture');

    // Round Trip Monitoring
    Route::get('/round_trip', [RoundTripController::class, 'index'])->name('round_trip.index');

    // Settings 
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // Profile Management
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/user/create', [SettingsController::class, 'createUser'])->name('settings.createUser');
    Route::delete('/settings/account/delete/{id}', [SettingsController::class, 'deleteUser'])->name('settings.account.delete');

    Route::post('/settings/salary/store', [SettingsController::class, 'storeSalary'])->name('settings.salary.store');
    Route::post('/settings/salary/update/{id}', [SettingsController::class, 'updateSalary'])->name('settings.salary.update');
    Route::delete('/settings/salary/delete/{id}', [SettingsController::class, 'deleteSalary'])->name('settings.salary.delete');
});

// Dispatcher Routes
Route::prefix('dispatcher')->name('dispatcher.')->group(function () {
    Route::get('/scan', [DispatcherController::class, 'scanFace'])->name('scan');
    Route::post('/scan/process', [DispatcherController::class, 'processScan'])->name('processScan');
});
