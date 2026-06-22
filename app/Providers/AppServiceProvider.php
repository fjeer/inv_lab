<?php

namespace App\Providers;

use App\Models\Building;
use App\Models\DamageReport;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\LabBorrowing;
use App\Models\Laboratory;
use App\Models\PatrolSchedule;
use App\Models\Procurement;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        // Soft-delete aware route model bindings for show methods
        $modelsWithTrashed = [
            'equipment' => Equipment::class,
            'laboratory' => Laboratory::class,
            'borrowing' => LabBorrowing::class,
            'procurement' => Procurement::class,
            'category' => EquipmentCategory::class,
            'building' => Building::class,
            'room' => Room::class,
            'damage_report' => DamageReport::class,
            'user' => User::class,
            'patrol_schedule' => PatrolSchedule::class,
        ];

        foreach ($modelsWithTrashed as $name => $modelClass) {
            Route::bind($name, function ($value) use ($modelClass) {
                return $modelClass::withTrashed()->findOrFail($value);
            });
        }
    }
}
