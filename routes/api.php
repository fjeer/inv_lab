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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
*/

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

    // Schedules routes removed since LabSchedule model and schedules table are deleted

    Route::middleware('role:admin_lab,asisten_lab,admin,asisten')->group(function () {
        // Equipment
        Route::get('/equipment', [EquipmentApiController::class, 'index']);
        Route::get('/equipment/{id}', [EquipmentApiController::class, 'show']);
        Route::post('/equipment/scan', [EquipmentApiController::class, 'scanQr']);
        Route::post('/equipment', [EquipmentApiController::class, 'store']);
        Route::put('/equipment/{id}', [EquipmentApiController::class, 'update']);
        Route::delete('/equipment/{id}', [EquipmentApiController::class, 'destroy']);
    });

    // Laboratories
    Route::get('/laboratories', [LaboratoryApiController::class, 'index']);
    Route::get('/laboratories/{id}', [LaboratoryApiController::class, 'show']);
    Route::post('/laboratories', [LaboratoryApiController::class, 'store']);
    Route::put('/laboratories/{id}', [LaboratoryApiController::class, 'update']);
    Route::delete('/laboratories/{id}', [LaboratoryApiController::class, 'destroy']);

    // Borrowings
    Route::get('/borrowings', [BorrowingApiController::class, 'index']);
    Route::get('/borrowings/{id}', [BorrowingApiController::class, 'show']);
    Route::post('/borrowings', [BorrowingApiController::class, 'store']);
    Route::post('/borrowings/{id}/approve', [BorrowingApiController::class, 'approve']);
    Route::post('/borrowings/{id}/reject', [BorrowingApiController::class, 'reject']);
    Route::post('/borrowings/{id}/complete', [BorrowingApiController::class, 'complete']);
    Route::post('/borrowings/{id}/cancel', [BorrowingApiController::class, 'cancel']);
    Route::delete('/borrowings/{id}', [BorrowingApiController::class, 'destroy']);

    // Procurements
    Route::get('/procurements', [ProcurementApiController::class, 'index']);
    Route::get('/procurements/{id}', [ProcurementApiController::class, 'show']);
    Route::post('/procurements', [ProcurementApiController::class, 'store']);
    Route::post('/procurements/{id}/approve', [ProcurementApiController::class, 'approve']);
    Route::post('/procurements/{id}/reject', [ProcurementApiController::class, 'reject']);
    Route::delete('/procurements/{id}', [ProcurementApiController::class, 'destroy']);

    // Categories
    Route::get('/categories', [CategoryApiController::class, 'index']);
    Route::get('/categories/{id}', [CategoryApiController::class, 'show']);
    Route::post('/categories', [CategoryApiController::class, 'store']);
    Route::put('/categories/{id}', [CategoryApiController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryApiController::class, 'destroy']);

    // Users
    Route::get('/users', [UserApiController::class, 'index']);
    Route::get('/users/{id}', [UserApiController::class, 'show']);
    Route::post('/users', [UserApiController::class, 'store']);
    Route::put('/users/{id}', [UserApiController::class, 'update']);
    Route::delete('/users/{id}', [UserApiController::class, 'destroy']);
    // Buildings
    Route::get('/buildings', [BuildingApiController::class, 'index']);
    Route::get('/buildings/{id}', [BuildingApiController::class, 'show']);
    Route::post('/buildings', [BuildingApiController::class, 'store']);
    Route::put('/buildings/{id}', [BuildingApiController::class, 'update']);
    Route::delete('/buildings/{id}', [BuildingApiController::class, 'destroy']);

    // Rooms
    Route::get('/rooms', [RoomApiController::class, 'index']);
    Route::get('/rooms/{id}', [RoomApiController::class, 'show']);
    Route::post('/rooms', [RoomApiController::class, 'store']);
    Route::put('/rooms/{id}', [RoomApiController::class, 'update']);
    Route::delete('/rooms/{id}', [RoomApiController::class, 'destroy']);

    // Conditions
    Route::get('/conditions', [ConditionApiController::class, 'index']);
    Route::post('/conditions', [ConditionApiController::class, 'store']);
    Route::delete('/conditions/{id}', [ConditionApiController::class, 'destroy']);

    // Damage Reports
    Route::get('/damage-reports', [DamageReportApiController::class, 'index']);
    Route::get('/damage-reports/{id}', [DamageReportApiController::class, 'show']);
    Route::post('/damage-reports', [DamageReportApiController::class, 'store']);
    Route::put('/damage-reports/{id}/status', [DamageReportApiController::class, 'updateStatus']);
    Route::delete('/damage-reports/{id}', [DamageReportApiController::class, 'destroy']);
    Route::delete('/damage-reports/{id}/force', [DamageReportApiController::class, 'forceDestroy']);

    // Patrol Schedules (Revisi)
    Route::get('/patrol-schedules', [PatrolScheduleApiController::class, 'index']);
    Route::get('/patrol-schedules/{id}', [PatrolScheduleApiController::class, 'show']);
    Route::post('/patrol-schedules', [PatrolScheduleApiController::class, 'store']);
    Route::put('/patrol-schedules/{id}', [PatrolScheduleApiController::class, 'update']);
    Route::delete('/patrol-schedules/{id}', [PatrolScheduleApiController::class, 'destroy']);
});
