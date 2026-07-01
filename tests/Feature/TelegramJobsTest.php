<?php

use App\Jobs\SendDailyPatrolReminders;
use App\Jobs\SendTestTelegram;
use App\Models\Laboratory;
use App\Models\PatrolSchedule;
use App\Models\User;
use App\Notifications\PatrolReminderNotification;
use App\Notifications\TestTelegramNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.telegram.bot_token', 'test-token');

    Http::fake([
        'https://api.telegram.org/bot*/sendMessage' => Http::response(['ok' => true]),
    ]);
});

it('sends reminders to asisten with schedule today', function () {
    Notification::fake();

    $asisten = User::factory()->create([
        'role' => 'asisten_lab',
        'telegram_chat_id' => '111',
    ]);

    $lab = Laboratory::factory()->create();
    PatrolSchedule::factory()->create([
        'user_id' => $asisten->id,
        'laboratory_id' => $lab->id,
        'day_of_week' => strtolower(now('Asia/Jakarta')->format('l')),
        'status' => 'active',
    ]);

    SendDailyPatrolReminders::dispatchSync();

    Notification::assertSentTo($asisten, PatrolReminderNotification::class);
});

it('does not send to asisten without telegram linked', function () {
    Notification::fake();

    $asisten = User::factory()->create([
        'role' => 'asisten_lab',
        'telegram_chat_id' => null,
    ]);

    $lab = Laboratory::factory()->create();
    PatrolSchedule::factory()->create([
        'user_id' => $asisten->id,
        'laboratory_id' => $lab->id,
        'day_of_week' => strtolower(now('Asia/Jakarta')->format('l')),
        'status' => 'active',
    ]);

    SendDailyPatrolReminders::dispatchSync();

    Notification::assertNothingSent();
});

it('sends summary to admin', function () {
    Notification::fake();

    $admin = User::factory()->create([
        'role' => 'admin_lab',
        'telegram_chat_id' => 'admin123',
    ]);

    $asisten = User::factory()->create(['role' => 'asisten_lab']);
    $lab = Laboratory::factory()->create();
    PatrolSchedule::factory()->create([
        'user_id' => $asisten->id,
        'laboratory_id' => $lab->id,
        'day_of_week' => strtolower(now('Asia/Jakarta')->format('l')),
        'status' => 'active',
    ]);

    SendDailyPatrolReminders::dispatchSync();

    Notification::assertSentTo($admin, PatrolReminderNotification::class);
});

it('test telegram sends to all linked users', function () {
    Notification::fake();

    $user1 = User::factory()->create(['telegram_chat_id' => '111']);
    $user2 = User::factory()->create(['telegram_chat_id' => '222']);
    User::factory()->create(['telegram_chat_id' => null]);

    SendTestTelegram::dispatchSync();

    Notification::assertSentTo($user1, TestTelegramNotification::class);
    Notification::assertSentTo($user2, TestTelegramNotification::class);
});
