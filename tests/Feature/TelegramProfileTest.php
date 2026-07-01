<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates link token for authenticated user', function () {
    $user = User::factory()->create(['telegram_verification_token' => null]);

    $response = $this->actingAs($user)->postJson('/profile/link-telegram');

    $response->assertOk();
    $response->assertJsonStructure(['success', 'data' => ['url', 'token']]);
    expect($response['data']['url'])->toContain('https://t.me/');
    expect($user->fresh()->telegram_verification_token)->not->toBeNull();
});

it('returns existing token if already generated', function () {
    $user = User::factory()->create(['telegram_verification_token' => 'existing-token']);

    $response = $this->actingAs($user)->postJson('/profile/link-telegram');

    $response->assertOk();
    expect($response['data']['token'])->toBe('existing-token');
});

it('unlinks telegram account', function () {
    $user = User::factory()->create(['telegram_chat_id' => '123456789']);

    $response = $this->actingAs($user)->postJson('/profile/unlink-telegram');

    $response->assertOk();
    expect($user->fresh()->telegram_chat_id)->toBeNull();
});

it('requires authentication for link/unlink', function () {
    $this->postJson('/profile/link-telegram')->assertUnauthorized();
    $this->postJson('/profile/unlink-telegram')->assertUnauthorized();
});

it('allows admin to send test notification', function () {
    $admin = User::factory()->create([
        'role' => 'admin_lab',
        'telegram_chat_id' => '123',
    ]);

    $response = $this->actingAs($admin)->postJson('/profile/test-telegram');

    $response->assertOk();
    $response->assertJson(['success' => true]);
});

it('prevents non-admin from sending test notification', function () {
    $asisten = User::factory()->create([
        'role' => 'asisten_lab',
        'telegram_chat_id' => '456',
    ]);

    $response = $this->actingAs($asisten)->postJson('/profile/test-telegram');

    $response->assertForbidden();
});
