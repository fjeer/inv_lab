<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class PatrolReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Collection $schedules,
        public string $type
    ) {}

    public function via($notifiable): array
    {
        return [\App\Channels\TelegramChannel::class];
    }

    public function toTelegram($notifiable): array
    {
        $message = $this->type === 'asisten'
            ? $this->formatForAsisten()
            : $this->formatForAdmin();

        return ['message' => $message];
    }

    private function formatForAsisten(): string
    {
        $schedule = $this->schedules->first();

        $text = "\xF0\x9F\x94\x94 PENGINGAT PATROL HARI INI\n\n";
        $text .= "Halo {$schedule->user->name},\n\n";
        $text .= "Anda memiliki jadwal patrol hari ini:\n\n";
        $text .= "\xF0\x9F\x93\x8D {$schedule->laboratory->name}\n";
        $text .= "\xE2\x8F\xB0 {$schedule->start_time} - {$schedule->end_time}\n\n";
        $text .= "Harap lakukan patrol sesuai jadwal.\n\n";
        $text .= "Terima kasih.\n";
        $text .= "\xE2\x94\x80\n";
        $text .= "Sistem Patrol Inventaris Lab";

        return $text;
    }

    private function formatForAdmin(): string
    {
        $now = now()->setTimezone('Asia/Jakarta');
        $days = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $dayName = $days[$now->format('l')] ?? $now->format('l');
        $date = $now->isoFormat('D MMMM YYYY', 'id');

        $text = "\xF0\x9F\x93\x8B LAPORAN PATROL HARI INI\n";
        $text .= "{$dayName}, {$date}\n\n";
        $text .= "Berikut daftar asisten yang bertugas:\n\n";

        $index = 1;
        foreach ($this->schedules as $schedule) {
            $text .= "{$index}. {$schedule->user->name}\n";
            $text .= "   \xF0\x9F\x93\x8D {$schedule->laboratory->name}\n";
            $text .= "   \xE2\x8F\xB0 {$schedule->start_time} - {$schedule->end_time}\n\n";
            $index++;
        }

        $text .= "Total: {$this->schedules->count()} asisten bertugas\n\n";
        $text .= "\xE2\x94\x80\n";
        $text .= "Sistem Patrol Inventaris Lab";

        return $text;
    }
}
