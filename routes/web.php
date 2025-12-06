<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
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
use App\Http\Controllers\Dispatcher\DashboardController;
use App\Http\Controllers\Dispatcher\ScanController;

/*
|--------------------------------------------------------------------------
| Authentication (Shared for Admin & Dispatcher)
|--------------------------------------------------------------------------
*/
// Redirect root "/" to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Login & Logout
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// OTP Routes
Route::get('/otp', [LoginController::class, 'showOtpForm'])->name('auth.otp.form');
Route::post('/otp', [LoginController::class, 'verifyOtp'])->name('auth.otp.verify');
Route::get('/otp/resend', [LoginController::class, 'resendOtp'])->name('auth.otp.resend');
Route::get('/login/success', function () {
    return view('auth.login-success');
})->name('login.success');
// Forgot Password
Route::get('/forgot-password', [VerifyController::class, 'showForm'])->name('auth.verify.form');
Route::post('/send-otp', [VerifyController::class, 'sendOtp'])->name('auth.password.sendOtp');
Route::get('/verify-otp', [VerifyController::class, 'showOtpForm'])->name('auth.verify.otp');
Route::post('/verify-otp', [VerifyController::class, 'verifyOtp'])->name('auth.verify.otp.submit');
Route::get('/reset-password', [VerifyController::class, 'showResetPasswordForm'])->name('auth.password.reset.form');
Route::post('/reset-password', [VerifyController::class, 'resetPassword'])->name('auth.password.reset.submit');
Route::post('/resend-otp', [VerifyController::class, 'resendOtp'])->name('auth.resend.otp');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class.':admin'])
    ->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/autocomplete', [AdminController::class, 'autocomplete'])->name('autocomplete');

    // Payroll
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/export/pdf', [PayrollController::class, 'exportPdf'])->name('payroll.export.pdf');
    Route::get('/payroll/print', [PayrollController::class, 'print'])->name('payroll.print');
    
    // Payslips
    Route::get('/payslips/view/{id}', [PayslipController::class, 'view'])->name('payslips.view');
    Route::get('/payslips/print/{id}', [PayslipController::class, 'print'])->name('payslips.print');
    Route::get('/payslips/download/{id}', [PayslipController::class, 'download'])->name('payslips.download');
    Route::get('/payslips/print-all', [PayslipController::class, 'bulkPrint'])->name('payslips.bulk.print');


    // Employees
    Route::resource('employees', EmployeeController::class)->except(['show']);
    Route::get('/employees/export/pdf', [EmployeeController::class, 'exportPDF'])->name('employees.export.pdf');
    Route::get('/employees/print', [EmployeeController::class, 'print'])->name('employees.print');
    Route::post('employees/make-admin/{id}', [EmployeeController::class, 'makeAdmin'])->name('employees.makeAdmin');
Route::post('/employees/check-duplicate', [EmployeeController::class, 'checkDuplicate'])->name('employees.checkDuplicate');


    // Deductions
    Route::resource('deductions', DeductionController::class)->except(['show']);
    Route::get('deductions/export/pdf', [DeductionController::class, 'exportPDF'])->name('deductions.export.pdf');
    Route::get('deductions/print', [DeductionController::class, 'print'])->name('deductions.print');

    // Holidays
    Route::resource('holidays', HolidayController::class)->except(['show']);

// Attendance
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::get('/attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');
Route::post('/attendance/capture', [AttendanceController::class, 'capture'])->name('attendance.capture');
Route::get('/attendance/today', [AttendanceController::class, 'todayAttendance'])->name('attendance.today');
Route::post('/attendance/end-shift', [AttendanceController::class, 'endShift'])->name('attendance.endShift');
Route::get('/attendance/status/{employeeId}', [AttendanceController::class, 'getShiftStatus']);
Route::post('/attendance/manual-store', [AttendanceController::class, 'manualStore'])->name('attendance.manual.store');
    // Round Trip
    Route::get('/round_trip', [RoundTripController::class, 'index'])->name('round_trip.index');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/change-password', [SettingsController::class, 'changePassword'])->name('settings.changePassword');
    Route::post('/settings/user/create', [SettingsController::class, 'createUser'])->name('settings.createUser');
    Route::delete('/settings/account/delete/{id}', [SettingsController::class, 'deleteUser'])->name('settings.account.delete');

    Route::post('/settings/salary/store', [SettingsController::class, 'storeSalary'])->name('settings.salary.store');
    Route::post('/settings/salary/update/{id}', [SettingsController::class, 'updateSalary'])->name('settings.salary.update');
    Route::delete('/settings/salary/delete/{id}', [SettingsController::class, 'deleteSalary'])->name('settings.salary.delete');
});

/*
|--------------------------------------------------------------------------
| Dispatcher Routes
|--------------------------------------------------------------------------
*/
Route::prefix('dispatcher')->name('dispatcher.')
    ->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class.':dispatcher'])
    ->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/change-password', [DashboardController::class, 'changePassword'])->name('change.password');
    // Scan
    // ✅ Scan Face
    Route::get('/scan/face', [ScanController::class, 'scanFace'])->name('scan.face');
    Route::post('/scan/face/capture', [ScanController::class, 'capture'])->name('scan.face.capture');

    // ✅ End Shift
    Route::post('/end-shift', [ScanController::class, 'endShift'])->name('end.shift');

    // ✅ Fetch today’s logs (used in Blade refresh)
    Route::get('/today/logs', [ScanController::class, 'todayLogs'])->name('today.logs');
    Route::get('/round-trip-history', [ScanController::class, 'roundTripHistory'])->name('roundtrip.history');
});

