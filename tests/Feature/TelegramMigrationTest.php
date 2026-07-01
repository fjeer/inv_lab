<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has telegram fields in users table', function () {
    $user = User::factory()->create([
        'telegram_chat_id' => '123456789',
        'telegram_verification_token' => 'test-token-abc',
    ]);

    expect($user->telegram_chat_id)->toBe('123456789');
    expect($user->telegram_verification_token)->toBe('test-token-abc');
});

it('knows when telegram is linked', function () {
    $user = User::factory()->create(['telegram_chat_id' => null]);
    expect($user->hasTelegramLinked())->toBeFalse();

    $user->update(['telegram_chat_id' => '123456789']);
    expect($user->fresh()->hasTelegramLinked())->toBeTrue();
});

it('enforces unique telegram_chat_id', function () {
    User::factory()->create(['telegram_chat_id' => '123456789']);
    $this->expectException(\Illuminate\Database\QueryException::class);
    User::factory()->create(['telegram_chat_id' => '123456789']);
});
