<?php

use App\Channels\TelegramChannel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

class TestNotification extends Notification
{
    public function via($notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram($notifiable): array
    {
        return ['message' => 'Test message from unit test'];
    }
}

it('sends message via telegram channel', function () {
    Http::fake([
        'https://api.telegram.org/bot*/sendMessage' => Http::response(['ok' => true]),
    ]);

    $user = User::factory()->create(['telegram_chat_id' => '123456789']);
    $channel = app(TelegramChannel::class);

    $channel->send($user, new TestNotification);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.telegram.org/bottest-token/sendMessage'
            && $request['chat_id'] === '123456789'
            && $request['text'] === 'Test message from unit test';
    });
});
