<?php

namespace App\Jobs;

use App\Models\PatrolSchedule;
use App\Models\User;
use App\Notifications\PatrolReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class SendDailyPatrolReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function handle(): void
    {
        $today = strtolower(now('Asia/Jakarta')->format('l'));

        $asistens = User::whereHas('patrolSchedules', function ($q) use ($today) {
            $q->where('status', 'active')
              ->where('day_of_week', $today);
        })->whereNotNull('telegram_chat_id')->get();

        foreach ($asistens as $asisten) {
            $schedules = $asisten->patrolSchedules()
                ->where('status', 'active')
                ->where('day_of_week', $today)
                ->with('laboratory')
                ->get();

            if ($schedules->isNotEmpty()) {
                $asisten->notify(new PatrolReminderNotification(
                    schedules: $schedules,
                    type: 'asisten'
                ));
            }
        }

        $allSchedules = PatrolSchedule::with(['user', 'laboratory'])
            ->where('status', 'active')
            ->where('day_of_week', $today)
            ->get();

        if ($allSchedules->isNotEmpty()) {
            $admins = User::where(function ($q) {
                $q->where('role', 'admin_lab')
                  ->orWhereHas('roleRelation', fn ($r) => $r->where('name', 'admin'));
            })->whereNotNull('telegram_chat_id')->get();

            foreach ($admins as $admin) {
                $admin->notify(new PatrolReminderNotification(
                    schedules: $allSchedules,
                    type: 'admin'
                ));
            }
        }
    }
}
