<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Trainer;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {

    // Shared: redirect sesuai role
    Route::get('/dashboard', function () {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('trainer.dashboard');
    })->name('dashboard');

    // ── TRAINER ROUTES ─────────────────────────
    Route::middleware(['role:Trainer Senior,Trainer Junior'])
        ->prefix('trainer')
        ->name('trainer.')
        ->group(function () {
            Route::get('/', [Trainer\DashboardController::class, 'index'])->name('dashboard');
            
            // Students
            Route::get('/students', [Trainer\StudentController::class, 'index'])->name('students.index');
            Route::get('/students/{student}', [Trainer\StudentController::class, 'show'])->name('students.show');
            
            // Attendance
            Route::get('/attendance', [Trainer\AttendanceController::class, 'index'])->name('attendance.index');
            Route::post('/attendance', [Trainer\AttendanceController::class, 'store'])->name('attendance.store');
            Route::get('/attendance/recap', [Trainer\AttendanceController::class, 'recap'])->name('attendance.recap');
            
            // Payslip
            Route::get('/payslip', [Trainer\PayslipController::class, 'index'])->name('payslip.index');
            Route::get('/payslip/{month}/{year}/pdf', [Trainer\PayslipController::class, 'exportPdf'])->name('payslip.pdf');
        });

// ── ADMIN ROUTES ──────────────────────────
    Route::middleware(['role:Admin,Management'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

            // Users CRUD (WEB - Render View)
            Route::get('/users', [Admin\AdminUserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [Admin\AdminUserController::class, 'create'])->name('users.create');

            // Users AJAX API
            Route::get('/users/search', [Admin\AdminUserController::class, 'search'])->name('users.search');
            Route::post('/users', [Admin\AdminUserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}', [Admin\AdminUserController::class, 'show'])->name('users.show');
            Route::put('/users/{user}', [Admin\AdminUserController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [Admin\AdminUserController::class, 'destroy'])->name('users.destroy');
            Route::post('/users/{user}/reset-password', [Admin\AdminUserController::class, 'resetPassword'])->name('users.reset-password');
            Route::post('/users/{user}/toggle-status', [Admin\AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');

// Students CRUD
            Route::resource('students', Admin\StudentController::class);
            
            // Students AJAX API (for inline editing)
            Route::patch('/students/{student}/inline', [Admin\StudentController::class, 'inlineUpdate'])->name('students.inline-update');
            Route::delete('/students/{student}/delete', [Admin\StudentController::class, 'inlineDelete'])->name('students.inline-delete');
            Route::get('/students/api/trainers', [Admin\StudentController::class, 'getTrainers'])->name('students.trainers');

            // Global Recap
            Route::get('recap', [Admin\RecapController::class, 'index'])->name('recap.index');
            Route::get('recap/export', [Admin\RecapController::class, 'export'])->name('recap.export');

            // Payroll
            Route::get('payroll', [Admin\PayrollController::class, 'index'])->name('payroll.index');
            Route::post('payroll/calculate', [Admin\PayrollController::class, 'calculate'])->name('payroll.calculate');
            Route::get('payroll/export', [Admin\PayrollController::class, 'export'])->name('payroll.export');

            // Salary Adjustments
            Route::resource('adjustments', Admin\SalaryAdjustmentController::class)->only(['index', 'store', 'destroy']);

            // Settings
            Route::get('settings', [Admin\SettingController::class, 'index'])->name('settings.index');
            Route::post('settings/tiers', [Admin\SettingController::class, 'updateTiers'])->name('settings.tiers');
            Route::post('settings/categories', [Admin\SettingController::class, 'storeCategory'])->name('settings.categories.store');
            Route::delete('settings/categories/{category}', [Admin\SettingController::class, 'deleteCategory'])->name('settings.categories.destroy');
        });
});

require __DIR__.'/auth.php';
