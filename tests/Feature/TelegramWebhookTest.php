<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.telegram.bot_token', 'test-token');
});

it('processes /start command and links telegram account', function () {
    $user = User::factory()->create([
        'telegram_verification_token' => 'valid-token-123',
        'telegram_chat_id' => null,
    ]);

    Http::fake([
        'https://api.telegram.org/bot*/sendMessage' => Http::response(['ok' => true]),
    ]);

    $response = $this->postJson('/api/telegram/webhook', [
        'message' => [
            'chat' => ['id' => 987654321],
            'text' => '/start valid-token-123',
        ],
    ]);

    $response->assertOk();
    expect($user->fresh()->telegram_chat_id)->toBe('987654321');
    expect($user->fresh()->telegram_verification_token)->toBeNull();
});

it('rejects invalid token', function () {
    $response = $this->postJson('/api/telegram/webhook', [
        'message' => [
            'chat' => ['id' => 123],
            'text' => '/start invalid-token',
        ],
    ]);

    $response->assertOk();
});

it('handles non-start messages gracefully', function () {
    $response = $this->postJson('/api/telegram/webhook', [
        'message' => [
            'chat' => ['id' => 123],
            'text' => 'just a regular message',
        ],
    ]);

    $response->assertOk();
});
