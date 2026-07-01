<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TestTelegramNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return [\App\Channels\TelegramChannel::class];
    }

    public function toTelegram($notifiable): array
    {
        return [
            'message' => "\xF0\x9F\x94\x94 Ini adalah pesan uji coba dari Sistem Patrol Inventaris Lab.\nNotifikasi Telegram berfungsi dengan baik! \xE2\x9C\x85",
        ];
    }
}
