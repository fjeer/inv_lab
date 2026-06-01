<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DamageReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentCategoryController;
use App\Http\Controllers\EquipmentConditionController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\EquipmentItemController;
use App\Http\Controllers\LabBorrowingController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\PatrolScheduleController;
use App\Livewire\Admin\QrCodePrint;
use App\Livewire\Admin\RoleManagement;
use App\Livewire\Asisten\PatrolExecution;
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
    Route::middleware('role:admin_lab,asisten_lab,admin,asisten')->group(function () {
        Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment.index');
        Route::get('/equipment/{equipment}', [EquipmentController::class, 'show'])->name('equipment.show');
        Route::delete('/equipment/{equipment}/items/{item}', [EquipmentItemController::class, 'destroy'])->name('equipment-items.destroy');
        Route::post('/equipment/{equipment}/items/{item}/restore', [EquipmentItemController::class, 'restore'])->name('equipment-items.restore');
        Route::delete('/equipment/{equipment}/items/{item}/force', [EquipmentItemController::class, 'forceDestroy'])->name('equipment-items.force-destroy');
    });

    // Borrowings
    Route::get('/borrowings', [LabBorrowingController::class, 'index'])->name('borrowings.index');
    Route::get('/borrowings/schedule', [LabBorrowingController::class, 'schedule'])->name('borrowings.schedule');
    Route::get('/borrowings/create', [LabBorrowingController::class, 'create'])->name('borrowings.create');
    Route::get('/borrowings/{borrowing}', [LabBorrowingController::class, 'show'])->name('borrowings.show');

    // Laboratories (read for all)
    Route::get('/laboratories', [LaboratoryController::class, 'index'])->name('laboratories.index');
    Route::get('/laboratories/{laboratory}', [LaboratoryController::class, 'show'])->name('laboratories.show');

    // Damage Reports (all roles can create/view)
    Route::get('/damage-reports', [DamageReportController::class, 'index'])->name('damage-reports.index');
    Route::get('/damage-reports/create', [DamageReportController::class, 'create'])->name('damage-reports.create');
    Route::post('/damage-reports', [DamageReportController::class, 'store'])->name('damage-reports.store');
    Route::get('/damage-reports/{damage_report}', [DamageReportController::class, 'show'])->withTrashed()->name('damage-reports.show');

    // Procurements (read/create for all)
    Route::get('/procurements', [ProcurementController::class, 'index'])->name('procurements.index');
    Route::get('/procurements/create', [ProcurementController::class, 'create'])->name('procurements.create');
    Route::post('/procurements', [ProcurementController::class, 'store'])->name('procurements.store');
    Route::get('/procurements/{procurement}', [ProcurementController::class, 'show'])->name('procurements.show');

    /*
    |----------------------------------------------------------------------
    | Admin & Asisten Routes
    |----------------------------------------------------------------------
    |*/
    Route::middleware('role:admin_lab,asisten_lab,admin,asisten')->group(function () {
        // Categories
        Route::get('/categories', [EquipmentCategoryController::class, 'index'])->name('categories.index');

        // Condition monitoring CRUD
        Route::get('/conditions', [EquipmentConditionController::class, 'index'])->name('conditions.index');
        Route::get('/conditions/{condition}', [EquipmentConditionController::class, 'show'])->name('conditions.show');
        Route::get('/conditions-create', [EquipmentConditionController::class, 'create'])->name('conditions.create');

        // Patrol Execution (Asisten)
        Route::get('/patrol/{scheduleId}', PatrolExecution::class)->name('patrol.execute');
    });

    /*
    |----------------------------------------------------------------------
    | Admin Only Routes
    |----------------------------------------------------------------------
    |*/
    Route::middleware('role:admin_lab,admin')->group(function () {
        // Buildings & Rooms CRUD
        Route::get('/buildings', [BuildingController::class, 'index'])->name('buildings.index');
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');

        // User Management
        Route::resource('users', UserController::class)->except(['show']);

        // Role & Permission Management (Livewire)
        Route::get('/roles', RoleManagement::class)->name('roles.index');

        // QR Code Print (Livewire)
        Route::get('/qr-codes', QrCodePrint::class)->name('qr-codes.index');

        // Patrol Schedule Management (Standard Ajax + DataTables)
        Route::get('/patrol-schedules', [PatrolScheduleController::class, 'index'])->name('patrol-schedules.index');
    });
});
