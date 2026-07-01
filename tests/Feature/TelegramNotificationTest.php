<?php

use App\Models\Laboratory;
use App\Models\PatrolSchedule;
use App\Models\User;
use App\Notifications\PatrolReminderNotification;
use App\Notifications\TestTelegramNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('formats patrol reminder for asisten', function () {
    $asisten = User::factory()->create(['name' => 'Budi']);
    $lab = Laboratory::factory()->create(['name' => 'Lab Fisika']);
    $schedule = PatrolSchedule::factory()->create([
        'user_id' => $asisten->id,
        'laboratory_id' => $lab->id,
        'day_of_week' => 'tuesday',
        'start_time' => '08:00',
        'end_time' => '10:00',
    ]);

    $notification = new PatrolReminderNotification(
        schedules: collect([$schedule->load('laboratory')]),
        type: 'asisten'
    );

    $data = $notification->toTelegram($asisten);

    expect($data['message'])->toContain('Budi');
    expect($data['message'])->toContain('Lab Fisika');
    expect($data['message'])->toContain('08:00');
    expect($data['message'])->toContain('10:00');
    expect($data['message'])->toContain('PENGINGAT PATROL HARI INI');
});

it('formats patrol reminder for admin', function () {
    $asisten1 = User::factory()->create(['name' => 'Budi']);
    $asisten2 = User::factory()->create(['name' => 'Sari']);
    $lab1 = Laboratory::factory()->create(['name' => 'Lab Fisika']);
    $lab2 = Laboratory::factory()->create(['name' => 'Lab Kimia']);
    $schedule1 = PatrolSchedule::factory()->create([
        'user_id' => $asisten1->id,
        'laboratory_id' => $lab1->id,
        'day_of_week' => 'tuesday',
        'start_time' => '08:00',
        'end_time' => '10:00',
    ]);
    $schedule2 = PatrolSchedule::factory()->create([
        'user_id' => $asisten2->id,
        'laboratory_id' => $lab2->id,
        'day_of_week' => 'tuesday',
        'start_time' => '10:00',
        'end_time' => '12:00',
    ]);

    $notification = new PatrolReminderNotification(
        schedules: collect([$schedule1->load('user', 'laboratory'), $schedule2->load('user', 'laboratory')]),
        type: 'admin'
    );

    $admin = User::factory()->create(['name' => 'Admin']);
    $data = $notification->toTelegram($admin);

    expect($data['message'])->toContain('LAPORAN PATROL HARI INI');
    expect($data['message'])->toContain('Budi');
    expect($data['message'])->toContain('Sari');
    expect($data['message'])->toContain('Lab Fisika');
    expect($data['message'])->toContain('Lab Kimia');
    expect($data['message'])->toContain('Total: 2');
});

it('formats test notification', function () {
    $notification = new TestTelegramNotification;
    $user = User::factory()->make(['name' => 'Admin']);

    $data = $notification->toTelegram($user);

    expect($data['message'])->toContain('pesan uji coba');
});
