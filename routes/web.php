<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Attendance Routes
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
    Route::post('/break-start', [AttendanceController::class, 'breakStart'])->name('break-start');
    Route::post('/break-end', [AttendanceController::class, 'breakEnd'])->name('break-end');
    Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');
});

require __DIR__.'/auth.php';