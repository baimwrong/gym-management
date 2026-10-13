<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DietPlanController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\FitnessClassController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LockerController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipPlanController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\WorkoutPlanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated App Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Attendance & Kiosk (Front Desk / Fast Check-in)
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/kiosk', [AttendanceController::class, 'kiosk'])->name('kiosk');
        Route::post('/process', [AttendanceController::class, 'process'])->name('process');
        Route::post('/{attendance}/checkout', [AttendanceController::class, 'checkOutById'])->name('checkout');
    });

    // Members Management
    Route::prefix('members')->name('members.')->group(function () {
        Route::get('/', [MemberController::class, 'index'])->name('index');
        Route::get('/create', [MemberController::class, 'create'])->name('create');
        Route::post('/', [MemberController::class, 'store'])->name('store');
        Route::get('/{member}', [MemberController::class, 'show'])->name('show');
        Route::get('/{member}/edit', [MemberController::class, 'edit'])->name('edit');
        Route::put('/{member}', [MemberController::class, 'update'])->name('update');
        Route::post('/{member}/metrics', [MemberController::class, 'storeMetric'])->name('metrics.store');
        Route::post('/{member}/renew', [MemberController::class, 'renew'])->name('renew');
        Route::post('/{member}/freeze', [MemberController::class, 'freeze'])->name('freeze');
        Route::get('/{member}/id-card', [MemberController::class, 'idCard'])->name('id-card');
    });

    // Membership Plans
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [MembershipPlanController::class, 'index'])->name('index');
        Route::get('/create', [MembershipPlanController::class, 'create'])->name('create');
        Route::post('/', [MembershipPlanController::class, 'store'])->name('store');
        Route::get('/{plan}/edit', [MembershipPlanController::class, 'edit'])->name('edit');
        Route::put('/{plan}', [MembershipPlanController::class, 'update'])->name('update');
        Route::post('/{plan}/toggle', [MembershipPlanController::class, 'toggle'])->name('toggle');
    });

    // Trainers & Coaching
    Route::prefix('trainers')->name('trainers.')->group(function () {
        Route::get('/', [TrainerController::class, 'index'])->name('index');
        Route::get('/{trainer}', [TrainerController::class, 'show'])->name('show');
        Route::post('/{trainer}/assign', [TrainerController::class, 'assignClient'])->name('assign');
        Route::post('/assignment/{assignment}/log-session', [TrainerController::class, 'logSession'])->name('log-session');
    });

    // Fitness Classes & Schedule
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', [FitnessClassController::class, 'index'])->name('index');
        Route::post('/', [FitnessClassController::class, 'store'])->name('store');
        Route::get('/{fitnessClass}', [FitnessClassController::class, 'show'])->name('show');
        Route::post('/{fitnessClass}/book', [FitnessClassController::class, 'book'])->name('book');
        Route::post('/booking/{booking}/attendance', [FitnessClassController::class, 'markAttendance'])->name('mark-attendance');
        Route::delete('/booking/{booking}', [FitnessClassController::class, 'cancelBooking'])->name('cancel');
    });

    // Workout Plans
    Route::prefix('workouts')->name('workouts.')->group(function () {
        Route::get('/', [WorkoutPlanController::class, 'index'])->name('index');
        Route::get('/create', [WorkoutPlanController::class, 'create'])->name('create');
        Route::post('/', [WorkoutPlanController::class, 'store'])->name('store');
        Route::get('/{workout}', [WorkoutPlanController::class, 'show'])->name('show');
        Route::post('/{workout}/assign', [WorkoutPlanController::class, 'assign'])->name('assign');
    });

    // Diet Plans
    Route::prefix('diets')->name('diets.')->group(function () {
        Route::get('/', [DietPlanController::class, 'index'])->name('index');
        Route::get('/create', [DietPlanController::class, 'create'])->name('create');
        Route::post('/', [DietPlanController::class, 'store'])->name('store');
        Route::get('/{diet}', [DietPlanController::class, 'show'])->name('show');
        Route::post('/{diet}/assign', [DietPlanController::class, 'assign'])->name('assign');
    });

    // Point of Sale (POS) & Pro Shop Inventory
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::get('/products', [PosController::class, 'products'])->name('products');
        Route::post('/products', [PosController::class, 'storeProduct'])->name('products.store');
        Route::put('/products/{product}', [PosController::class, 'updateProduct'])->name('products.update');
    });

    // Invoices & Billing
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::post('/{invoice}/pay', [InvoiceController::class, 'markPaid'])->name('mark-paid');
    });

    // Lockers
    Route::prefix('lockers')->name('lockers.')->group(function () {
        Route::get('/', [LockerController::class, 'index'])->name('index');
        Route::post('/{locker}/assign', [LockerController::class, 'assign'])->name('assign');
        Route::post('/{locker}/release', [LockerController::class, 'release'])->name('release');
        Route::post('/{locker}/status', [LockerController::class, 'setStatus'])->name('set-status');
    });

    // Equipment & Maintenance
    Route::prefix('equipment')->name('equipment.')->group(function () {
        Route::get('/', [EquipmentController::class, 'index'])->name('index');
        Route::post('/', [EquipmentController::class, 'store'])->name('store');
        Route::post('/{equipment}/status', [EquipmentController::class, 'updateStatus'])->name('update-status');
        Route::post('/{equipment}/log-service', [EquipmentController::class, 'logService'])->name('log-service');
    });

    // Reports & Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export/members', [ReportController::class, 'exportMembers'])->name('export-members');
        Route::get('/export/invoices', [ReportController::class, 'exportInvoices'])->name('export-invoices');
    });

    // Data Import Center (Admin / Manager only)
    Route::prefix('import')->name('import.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [\App\Http\Controllers\ImportController::class, 'index'])->name('index');
        Route::get('/sample/{type}', [\App\Http\Controllers\ImportController::class, 'downloadSample'])->name('sample');
        Route::post('/members', [\App\Http\Controllers\ImportController::class, 'importMembers'])->name('members');
        Route::post('/products', [\App\Http\Controllers\ImportController::class, 'importProducts'])->name('products');
        Route::post('/equipment', [\App\Http\Controllers\ImportController::class, 'importEquipment'])->name('equipment');
    });

    // System Settings (Admin / Manager only)
    Route::prefix('settings')->name('settings.')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/', [SettingController::class, 'update'])->name('update');
    });
});
