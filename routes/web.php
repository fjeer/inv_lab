<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DamageReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentCategoryController;
use App\Http\Controllers\EquipmentConditionController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\LabBorrowingController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\LabScheduleController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (all roles)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Equipment (restricted to non-pengguna)
    Route::middleware('role:admin_lab,asisten_lab')->group(function () {
        Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment.index');
        Route::get('/equipment/{equipment}', [EquipmentController::class, 'show'])->name('equipment.show');
    });

    // Schedules (read for all)
    Route::get('/schedules', [LabScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/{schedule}', [LabScheduleController::class, 'show'])->name('schedules.show');

    // Borrowings (all roles can view, pengguna can create)
    Route::get('/borrowings', [LabBorrowingController::class, 'index'])->name('borrowings.index');
    Route::get('/borrowings/schedule', [LabBorrowingController::class, 'schedule'])->name('borrowings.schedule');
    Route::get('/borrowings/create', [LabBorrowingController::class, 'create'])->name('borrowings.create');
    Route::post('/borrowings', [LabBorrowingController::class, 'store'])->name('borrowings.store');
    Route::get('/borrowings/{borrowing}', [LabBorrowingController::class, 'show'])->name('borrowings.show');
    Route::post('/borrowings/{borrowing}/cancel', [LabBorrowingController::class, 'cancel'])->name('borrowings.cancel');

    // Laboratories (read for all)
    Route::get('/laboratories', [LaboratoryController::class, 'index'])->name('laboratories.index');
    Route::get('/laboratories/{laboratory}', [LaboratoryController::class, 'show'])->name('laboratories.show');

    // Damage Reports (all roles can create/view)
    Route::get('/damage-reports', [DamageReportController::class, 'index'])->name('damage-reports.index');
    Route::get('/damage-reports/create', [DamageReportController::class, 'create'])->name('damage-reports.create');
    Route::post('/damage-reports', [DamageReportController::class, 'store'])->name('damage-reports.store');
    Route::get('/damage-reports/{damage_report}', [DamageReportController::class, 'show'])->name('damage-reports.show');

    // Procurements (read/create for all)
    Route::get('/procurements', [ProcurementController::class, 'index'])->name('procurements.index');
    Route::get('/procurements/create', [ProcurementController::class, 'create'])->name('procurements.create');
    Route::post('/procurements', [ProcurementController::class, 'store'])->name('procurements.store');
    Route::get('/procurements/{procurement}', [ProcurementController::class, 'show'])->name('procurements.show');

    /*
    |----------------------------------------------------------------------
    | Admin & Asisten Routes
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin_lab,asisten_lab')->group(function () {
        // Equipment Management
        Route::get('/equipment-create', [EquipmentController::class, 'create'])->name('equipment.create');
        Route::post('/equipment', [EquipmentController::class, 'store'])->name('equipment.store');
        Route::get('/equipment/{equipment}/edit', [EquipmentController::class, 'edit'])->name('equipment.edit');
        Route::put('/equipment/{equipment}', [EquipmentController::class, 'update'])->name('equipment.update');
        Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');

        // Categories
        Route::resource('categories', EquipmentCategoryController::class)->except(['show']);

        // Schedules CRUD
        Route::get('/schedules-create', [LabScheduleController::class, 'create'])->name('schedules.create');
        Route::post('/schedules', [LabScheduleController::class, 'store'])->name('schedules.store');
        Route::get('/schedules/{schedule}/edit', [LabScheduleController::class, 'edit'])->name('schedules.edit');
        Route::put('/schedules/{schedule}', [LabScheduleController::class, 'update'])->name('schedules.update');
        Route::delete('/schedules/{schedule}', [LabScheduleController::class, 'destroy'])->name('schedules.destroy');

        // Borrowing management (approve/reject/complete/delete)
        Route::post('/borrowings/{borrowing}/approve', [LabBorrowingController::class, 'approve'])->name('borrowings.approve');
        Route::post('/borrowings/{borrowing}/reject', [LabBorrowingController::class, 'reject'])->name('borrowings.reject');
        Route::post('/borrowings/{borrowing}/complete', [LabBorrowingController::class, 'complete'])->name('borrowings.complete');
        Route::delete('/borrowings/{borrowing}', [LabBorrowingController::class, 'destroy'])->name('borrowings.destroy');

        // Condition monitoring CRUD
        Route::get('/conditions', [EquipmentConditionController::class, 'index'])->name('conditions.index');
        Route::get('/conditions/{condition}', [EquipmentConditionController::class, 'show'])->name('conditions.show');
        Route::get('/conditions-create', [EquipmentConditionController::class, 'create'])->name('conditions.create');
        Route::post('/conditions', [EquipmentConditionController::class, 'store'])->name('conditions.store');

        // Damage report status management
        Route::put('/damage-reports/{damage_report}/status', [DamageReportController::class, 'updateStatus'])->name('damage-reports.update-status');
    });

    /*
    |----------------------------------------------------------------------
    | Admin Only Routes
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin_lab')->group(function () {
        // Laboratories CRUD (Management)
        Route::get('/laboratories/create', [LaboratoryController::class, 'create'])->name('laboratories.create');
        Route::post('/laboratories', [LaboratoryController::class, 'store'])->name('laboratories.store');
        Route::get('/laboratories/{laboratory}/edit', [LaboratoryController::class, 'edit'])->name('laboratories.edit');
        Route::put('/laboratories/{laboratory}', [LaboratoryController::class, 'update'])->name('laboratories.update');
        Route::delete('/laboratories/{laboratory}', [LaboratoryController::class, 'destroy'])->name('laboratories.destroy');

        // Buildings & Rooms CRUD
        Route::resource('buildings', BuildingController::class);
        Route::resource('rooms', RoomController::class);

        // User Management
        Route::resource('users', UserController::class)->except(['show']);

        // Procurement approve/reject/delete
        Route::post('/procurements/{procurement}/approve', [ProcurementController::class, 'approve'])->name('procurements.approve');
        Route::post('/procurements/{procurement}/reject', [ProcurementController::class, 'reject'])->name('procurements.reject');
        Route::delete('/procurements/{procurement}', [ProcurementController::class, 'destroy'])->name('procurements.destroy');
    });
});
