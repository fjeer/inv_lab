<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        $chatId = $notifiable->telegram_chat_id;

        if (! $chatId) {
            Log::warning('TelegramChannel: No chat_id for notifiable', [
                'notifiable_id' => $notifiable->id ?? null,
            ]);
            return;
        }

        $data = $notification->toTelegram($notifiable);

        $botToken = config('services.telegram.bot_token');

        $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $data['message'],
            'parse_mode' => 'HTML',
        ]);

        if (! $response->successful() || ! ($response['ok'] ?? false)) {
            Log::warning('TelegramChannel: Failed to send message', [
                'chat_id' => $chatId,
                'response' => $response->body(),
            ]);
        }
    }
}
