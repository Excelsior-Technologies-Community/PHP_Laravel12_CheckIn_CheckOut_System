<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('attendance');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // 1. Attendance Core
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::get('/attendance/export', [AttendanceController::class, 'exportCsv'])->name('attendance.export');
    Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
    Route::post('/break-start', [AttendanceController::class, 'breakStart'])->name('break-start');
    Route::post('/break-end', [AttendanceController::class, 'breakEnd'])->name('break-end');
    Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');

    // 2. Feature 4: Interactive Monthly Attendance Calendar
    Route::get('/attendance/calendar', [AttendanceController::class, 'calendar'])->name('attendance.calendar');

    // 3. Feature 8: Printable Monthly Timesheet
    Route::get('/attendance/timesheet', [AttendanceController::class, 'timesheet'])->name('attendance.timesheet');

    // 4. Feature 2: Leave Management & Absence Requests
    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::post('/leaves', [LeaveController::class, 'store'])->name('leaves.store');
    Route::delete('/leaves/{leave}', [LeaveController::class, 'destroy'])->name('leaves.destroy');

    // 5. Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/leaves', [LeaveController::class, 'adminIndex'])->name('leaves.index');
        Route::post('/leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
        Route::post('/leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');
        Route::get('/attendance/export-all', [AttendanceController::class, 'exportAllCsv'])->name('attendance.export-all');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
