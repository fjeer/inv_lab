<?php

namespace App\Actions;

use App\Models\ActivityLog;
use App\Models\PatrolSchedule;
use App\Support\DTOs\CreatePatrolScheduleData;

final class CreatePatrolScheduleAction
{
    public function handle(CreatePatrolScheduleData $data): PatrolSchedule
    {
        $schedule = PatrolSchedule::create([
            'user_id' => $data->userId,
            'laboratory_id' => $data->laboratoryId,
            'day_of_week' => $data->dayOfWeek,
            'start_time' => $data->startTime,
            'end_time' => $data->endTime,
            'status' => $data->status,
            'notes' => $data->notes,
        ]);

        $schedule->load(['user', 'laboratory']);

        ActivityLog::log(
            'create_patrol_schedule',
            "Membuat jadwal patroli untuk asisten {$schedule->user?->name} di {$schedule->laboratory?->name}",
            $schedule,
        );

        return $schedule;
    }
}
