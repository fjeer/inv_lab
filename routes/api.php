<?php

use App\Http\Controllers\Api\EquipmentApiController;
use App\Http\Controllers\Api\LaboratoryApiController;
use App\Http\Controllers\Api\BorrowingApiController;
use App\Http\Controllers\Api\ProcurementApiController;
use App\Http\Controllers\Api\UtilityApiController;

use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\BuildingApiController;
use App\Http\Controllers\Api\RoomApiController;
use App\Http\Controllers\Api\ConditionApiController;
use App\Http\Controllers\Api\DamageReportApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\PatrolScheduleApiController;
use Illuminate\Support\Facades\Route;

// Public Auth API
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Profile Settings
    Route::put('/profile', [ProfileApiController::class, 'update']);
    Route::put('/profile/password', [ProfileApiController::class, 'updatePassword']);

    // Dashboard Stats
    Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);

    // Utilities
    Route::get('/options', [UtilityApiController::class, 'options']);

    Route::middleware('role:admin_lab,asisten_lab,admin,asisten')->group(function () {
        // Equipment
        Route::get('/equipment', [EquipmentApiController::class, 'index']);
        Route::get('/equipment/{equipment}', [EquipmentApiController::class, 'show']);
        Route::post('/equipment/scan', [EquipmentApiController::class, 'scanQr']);
        Route::post('/equipment', [EquipmentApiController::class, 'store']);
        Route::put('/equipment/{equipment}', [EquipmentApiController::class, 'update']);
        Route::delete('/equipment/{equipment}', [EquipmentApiController::class, 'destroy']);
    });

    // Laboratories
    Route::get('/laboratories', [LaboratoryApiController::class, 'index']);
    Route::get('/laboratories/{laboratory}', [LaboratoryApiController::class, 'show']);
    Route::post('/laboratories', [LaboratoryApiController::class, 'store']);
    Route::put('/laboratories/{laboratory}', [LaboratoryApiController::class, 'update']);
    Route::delete('/laboratories/{laboratory}', [LaboratoryApiController::class, 'destroy']);

    // Borrowings
    Route::get('/borrowings', [BorrowingApiController::class, 'index']);
    Route::get('/borrowings/{borrowing}', [BorrowingApiController::class, 'show']);
    Route::post('/borrowings', [BorrowingApiController::class, 'store']);
    Route::post('/borrowings/{borrowing}/approve', [BorrowingApiController::class, 'approve']);
    Route::post('/borrowings/{borrowing}/reject', [BorrowingApiController::class, 'reject']);
    Route::post('/borrowings/{borrowing}/complete', [BorrowingApiController::class, 'complete']);
    Route::post('/borrowings/{borrowing}/cancel', [BorrowingApiController::class, 'cancel']);
    Route::delete('/borrowings/{borrowing}', [BorrowingApiController::class, 'destroy']);

    // Procurements
    Route::get('/procurements', [ProcurementApiController::class, 'index']);
    Route::get('/procurements/{procurement}', [ProcurementApiController::class, 'show']);
    Route::post('/procurements', [ProcurementApiController::class, 'store']);
    Route::post('/procurements/{procurement}/approve', [ProcurementApiController::class, 'approve']);
    Route::post('/procurements/{procurement}/reject', [ProcurementApiController::class, 'reject']);
    Route::delete('/procurements/{procurement}', [ProcurementApiController::class, 'destroy']);

    // Categories
    Route::get('/categories', [CategoryApiController::class, 'index']);
    Route::get('/categories/{category}', [CategoryApiController::class, 'show']);
    Route::post('/categories', [CategoryApiController::class, 'store']);
    Route::put('/categories/{category}', [CategoryApiController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryApiController::class, 'destroy']);

    // Users
    Route::get('/users', [UserApiController::class, 'index']);
    Route::get('/users/{user}', [UserApiController::class, 'show']);
    Route::post('/users', [UserApiController::class, 'store']);
    Route::put('/users/{user}', [UserApiController::class, 'update']);
    Route::delete('/users/{user}', [UserApiController::class, 'destroy']);

    // Buildings
    Route::get('/buildings', [BuildingApiController::class, 'index']);
    Route::get('/buildings/{building}', [BuildingApiController::class, 'show']);
    Route::post('/buildings', [BuildingApiController::class, 'store']);
    Route::put('/buildings/{building}', [BuildingApiController::class, 'update']);
    Route::delete('/buildings/{building}', [BuildingApiController::class, 'destroy']);

    // Rooms
    Route::get('/rooms', [RoomApiController::class, 'index']);
    Route::get('/rooms/{room}', [RoomApiController::class, 'show']);
    Route::post('/rooms', [RoomApiController::class, 'store']);
    Route::put('/rooms/{room}', [RoomApiController::class, 'update']);
    Route::delete('/rooms/{room}', [RoomApiController::class, 'destroy']);

    // Conditions
    Route::get('/conditions', [ConditionApiController::class, 'index']);
    Route::post('/conditions', [ConditionApiController::class, 'store']);
    Route::delete('/conditions/{condition}', [ConditionApiController::class, 'destroy']);

    // Damage Reports
    Route::get('/damage-reports', [DamageReportApiController::class, 'index']);
    Route::get('/damage-reports/{damage_report}', [DamageReportApiController::class, 'show']);
    Route::post('/damage-reports', [DamageReportApiController::class, 'store']);
    Route::put('/damage-reports/{damage_report}/status', [DamageReportApiController::class, 'updateStatus']);
    Route::delete('/damage-reports/{damage_report}', [DamageReportApiController::class, 'destroy']);
    Route::delete('/damage-reports/{id}/force', [DamageReportApiController::class, 'forceDestroy']);

    // Patrol Schedules
    Route::get('/patrol-schedules', [PatrolScheduleApiController::class, 'index']);
    Route::get('/patrol-schedules/{patrol_schedule}', [PatrolScheduleApiController::class, 'show']);
    Route::post('/patrol-schedules', [PatrolScheduleApiController::class, 'store']);
    Route::put('/patrol-schedules/{patrol_schedule}', [PatrolScheduleApiController::class, 'update']);
    Route::delete('/patrol-schedules/{patrol_schedule}', [PatrolScheduleApiController::class, 'destroy']);
});
