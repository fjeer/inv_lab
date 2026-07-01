# Notifikasi Telegram — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan fitur notifikasi Telegram otomatis — pengingat patrol jam 06:00 WIB untuk asisten + laporan ke admin + verifikasi akun via tombol "Hubungkan ke Telegram".

**Architecture:** Custom `TelegramChannel` untuk Laravel Notification system + Queue Job mingguan yang di-schedule tiap jam 23:00 UTC. Webhook endpoint untuk handle `/start {token}` dari bot Telegram. Verifikasi via UUID token sekali pakai, disimpan di kolom `telegram_verification_token` tabel users.

**Tech Stack:** Laravel 13, PHP 8.3+, Pest (testing), Redis (queue), MySQL, Telegram Bot API (HTTP tanpa SDK tambahan).

## Global Constraints

- Zero dependency tambahan — pakai `Http` facade Laravel yang sudah include Guzzle
- Semua pesan Telegram dalam Bahasa Indonesia
- Nama bot dinamis via konfigurasi (atau pakai `@namabot` di code)
- Waktu 06:00 WIB = 23:00 UTC (app timezone = 'UTC' di config/app.php)
- Migration menggunakan SQLite untuk testing via phpunit.xml
- Semua response JSON menggunakan struktur `success/data/message` yang konsisten dengan API existing

---

### Task 1: Migration & Model Update

**Files:**
- Create: `database/migrations/2026_07_01_000001_add_telegram_fields_to_users_table.php`
- Modify: `app/Models/User.php`
- Test: `tests/Feature/TelegramMigrationTest.php`

**Interfaces:**
- Produces: Kolom `telegram_chat_id` (string, nullable, unique) dan `telegram_verification_token` (string, nullable, unique) di tabel `users`
- Produces: Property `$fillable`, `$casts` di User model diperbarui
- Produces: Method `User::hasTelegramLinked(): bool`

- [ ] **Step 1: Write the failing test**

```php
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
```

Tulis ke `tests/Feature/TelegramMigrationTest.php`.

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/pest tests/Feature/TelegramMigrationTest.php -v`
Expected: FAIL — kolom `telegram_chat_id` dan method `hasTelegramLinked()` belum ada

- [ ] **Step 3: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->unique()->after('phone');
            $table->string('telegram_verification_token')->nullable()->unique()->after('telegram_chat_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telegram_chat_id', 'telegram_verification_token']);
        });
    }
};
```

Jalankan: `php artisan migrate`

- [ ] **Step 4: Update User model**

Di `app/Models/User.php`:

Update fillable attribute (line 16):
```php
#[Fillable(['name', 'email', 'password', 'role', 'role_id', 'nim_nip', 'phone', 'department', 'avatar', 'is_active', 'telegram_chat_id', 'telegram_verification_token'])]
```

Tambah method helper setelah method `getRoleLabelAttribute()` (sebelum `/* ---- Relationships ---- */`):
```php
public function hasTelegramLinked(): bool
{
    return ! is_null($this->telegram_chat_id);
}
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Feature/TelegramMigrationTest.php -v`
Expected: PASS

- [ ] **Step 6: Create LaboratoryFactory** (dibutuhkan Task 3 & 6)

Cek fillable model Laboratorium:
```bash
rg 'protected \$fillable' app/Models/Laboratory.php
```

Buat `database/factories/LaboratoryFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Laboratory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaboratoryFactory extends Factory
{
    protected $model = Laboratory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company() . ' Lab',
            'code' => fake()->unique()->bothify('LAB-####'),
            'location' => fake()->address(),
            'capacity' => fake()->numberBetween(10, 50),
            'responsible_person_id' => User::factory(),
            'status' => 'active',
        ];
    }
}
```

Catatan: `room_id` dan `description` sengaja di-skip (nullable di DB).

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_07_01_000001_add_telegram_fields_to_users_table.php app/Models/User.php tests/Feature/TelegramMigrationTest.php database/factories/LaboratoryFactory.php
git commit -m "feat: add telegram fields to users table and model"
```

---

### Task 2: Konfigurasi & Custom Notification Channel

**Files:**
- Modify: `config/services.php`
- Modify: `.env`
- Create: `app/Channels/TelegramChannel.php`
- Test: `tests/Feature/TelegramChannelTest.php`

**Interfaces:**
- Produces: `config('services.telegram.bot_token')` — token bot
- Produces: `TelegramChannel::send($notifiable, Notification $notification)` — kirim pesan via API
- Consumes: `$notifiable->telegram_chat_id` dari Task 1

- [ ] **Step 1: Write the failing test**

```php
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
```

Tulis ke `tests/Feature/TelegramChannelTest.php`.

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/pest tests/Feature/TelegramChannelTest.php -v`
Expected: FAIL — TelegramChannel class tidak ditemukan

- [ ] **Step 3: Add config**

Di `config/services.php`, tambah sebelum `]` penutup:
```php
'telegram' => [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'bot_name' => env('TELEGRAM_BOT_NAME', 'inv_lab_bot'),
],
```

Di `.env`, tambah:
```
TELEGRAM_BOT_TOKEN=8535256556:AAHHjRU9Pqvclvab2-rt7M34NwW7IiLOYi4
TELEGRAM_BOT_NAME=inv_lab_bot
```

Di `.env.example`:
```
TELEGRAM_BOT_TOKEN=
TELEGRAM_BOT_NAME=
```

- [ ] **Step 4: Create TelegramChannel**

```php
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
```

- [ ] **Step 5: Update phpunit.xml / Pest.php untuk mock config**

Di `tests/Pest.php`, tambah:
```php
uses()
    ->beforeEach(function () {
        config()->set('services.telegram.bot_token', 'test-token');
        config()->set('services.telegram.bot_name', 'test_bot');
    })
    ->in('Feature');
```

Jadi file `tests/Pest.php` menjadi:
```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

pest()
    ->beforeEach(function () {
        config()->set('services.telegram.bot_token', 'test-token');
        config()->set('services.telegram.bot_name', 'test_bot');
    })
    ->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function something()
{
    // ..
}
```

- [ ] **Step 6: Run tests**

Run: `vendor/bin/pest tests/Feature/TelegramChannelTest.php -v`
Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add config/services.php .env .env.example app/Channels/TelegramChannel.php tests/Feature/TelegramChannelTest.php tests/Pest.php
git commit -m "feat: add telegram config and custom notification channel"
```

---

### Task 3: Notification Classes

**Files:**
- Create: `app/Notifications/PatrolReminderNotification.php`
- Create: `app/Notifications/TestTelegramNotification.php`
- Test: `tests/Feature/TelegramNotificationTest.php`

**Interfaces:**
- Produces: `PatrolReminderNotification` — format pesan untuk asisten (satu jadwal) dan admin (semua jadwal)
- Produces: `TestTelegramNotification` — format pesan test sederhana
- Consumes: `TelegramChannel` dari Task 2
- Consumes: `$schedules` (Collection of PatrolSchedule) dengan relasi `user`, `laboratory`
- Consumes: `$type` ('asisten' | 'admin')

- [ ] **Step 1: Write failing tests**

```php
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
```

- [ ] **Step 2: Run to verify fail**

Run: `vendor/bin/pest tests/Feature/TelegramNotificationTest.php -v`
Expected: FAIL — class not found

- [ ] **Step 3: Create PatrolReminderNotification**

```php
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

        $text = "🔔 PENGINGAT PATROL HARI INI\n\n";
        $text .= "Halo {$schedule->user->name},\n\n";
        $text .= "Anda memiliki jadwal patrol hari ini:\n\n";
        $text .= "📍 {$schedule->laboratory->name}\n";
        $text .= "⏰ {$schedule->start_time} - {$schedule->end_time}\n\n";
        $text .= "Harap lakukan patrol sesuai jadwal.\n\n";
        $text .= "Terima kasih.\n";
        $text .= "─\n";
        $text .= "Sistem Patrol Inventaris Lab";

        return $text;
    }

    private function formatForAdmin(): string
    {
        $now = now()->setTimezone('Asia/Jakarta');
        $days = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $dayName = $days[$now->format('l')] ?? $now->format('l');
        $date = $now->isoFormat('D MMMM YYYY', 'id');

        $text = "📋 LAPORAN PATROL HARI INI\n";
        $text .= "{$dayName}, {$date}\n\n";
        $text .= "Berikut daftar asisten yang bertugas:\n\n";

        $index = 1;
        foreach ($this->schedules as $schedule) {
            $text .= "{$index}. {$schedule->user->name}\n";
            $text .= "   📍 {$schedule->laboratory->name}\n";
            $text .= "   ⏰ {$schedule->start_time} - {$schedule->end_time}\n\n";
            $index++;
        }

        $text .= "Total: {$this->schedules->count()} asisten bertugas\n\n";
        $text .= "─\n";
        $text .= "Sistem Patrol Inventaris Lab";

        return $text;
    }
}
```

- [ ] **Step 4: Create TestTelegramNotification**

```php
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
            'message' => "🔔 Ini adalah pesan uji coba dari Sistem Patrol Inventaris Lab.\nNotifikasi Telegram berfungsi dengan baik! ✅",
        ];
    }
}
```

- [ ] **Step 5: Create PatrolSchedule factory**

Periksa apakah factory `PatrolScheduleFactory` sudah ada. Jika belum:

```php
<?php

namespace Database\Factories;

use App\Models\Laboratory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatrolScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'laboratory_id' => Laboratory::factory(),
            'day_of_week' => fake()->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'status' => 'active',
        ];
    }
}
```

- [ ] **Step 6: Run tests**

Run: `vendor/bin/pest tests/Feature/TelegramNotificationTest.php -v`
Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add app/Notifications/PatrolReminderNotification.php app/Notifications/TestTelegramNotification.php tests/Feature/TelegramNotificationTest.php database/factories/PatrolScheduleFactory.php
git commit -m "feat: add patrol reminder and test notification classes"
```

---

### Task 4: Webhook Controller (Bot Verification)

**Files:**
- Create: `app/Http/Controllers/TelegramWebhookController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/TelegramWebhookTest.php`

**Interfaces:**
- Produces: `POST /api/telegram/webhook` — endpoint publik untuk menangani pesan dari bot
- Produces: Flow `/start {token}` → cari user → simpan chat_id → balas pesan konfirmasi
- Consumes: `User::$telegram_verification_token` dari Task 1
- Produces: `User::$telegram_chat_id` diisi

- [ ] **Step 1: Write failing tests**

```php
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
    // Response should contain error message (sent as Telegram reply)
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
```

- [ ] **Step 2: Run to verify fail**

Run: `vendor/bin/pest tests/Feature/TelegramWebhookTest.php -v`
Expected: FAIL — controller tidak ditemukan

- [ ] **Step 3: Create webhook controller**

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request): void
    {
        $message = $request->input('message.text', '');
        $chatId = $request->input('message.chat.id');

        if (! $chatId || ! str_starts_with($message, '/start ')) {
            return;
        }

        $token = trim(substr($message, 7));

        $user = User::where('telegram_verification_token', $token)->first();

        $botToken = config('services.telegram.bot_token');

        if (! $user) {
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => "❌ Token tidak valid atau sudah kedaluwarsa.\nSilakan coba lagi dari aplikasi.",
            ]);

            Log::warning('TelegramWebhook: Invalid token', ['token' => $token]);
            return;
        }

        $user->update([
            'telegram_chat_id' => $chatId,
            'telegram_verification_token' => null,
        ]);

        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => "✅ Akun Telegram berhasil dihubungkan ke {$user->name}!\n\nSekarang Anda akan menerima notifikasi patrol dari Sistem Patrol Inventaris Lab.",
        ]);

        Log::info('TelegramWebhook: Account linked', [
            'user_id' => $user->id,
            'chat_id' => $chatId,
        ]);
    }
}
```

- [ ] **Step 4: Add route**

Di `routes/web.php`, tambah di luar group middleware (endpoint publik):
```php
// Telegram Webhook (public)
Route::post('/api/telegram/webhook', [TelegramWebhookController::class, 'handle']);
```

Letakkan setelah `use` statements, sebelum guest routes.

- [ ] **Step 5: Add route to csrf exclusion**

Di `app/Http/Middleware/VerifyCsrfToken.php` (atau `bootstrap/app.php` untuk Laravel 11+), tambah `/api/telegram/webhook` ke pengecualian CSRF.

Cek dulu file `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        '/api/telegram/webhook',
    ]);
})
```

- [ ] **Step 6: Run tests**

Run: `vendor/bin/pest tests/Feature/TelegramWebhookTest.php -v`
Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/TelegramWebhookController.php routes/web.php tests/Feature/TelegramWebhookTest.php
git commit -m "feat: add telegram webhook handler for /start verification"
```

---

### Task 5: Profile Controller — Link/Unlink/Test

**Files:**
- Modify: `app/Http/Controllers/ProfileController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/TelegramProfileTest.php`

**Interfaces:**
- Produces: `POST /profile/link-telegram` — generate token, return deep link URL
- Produces: `POST /profile/unlink-telegram` — hapus chat_id
- Produces: `POST /profile/test-telegram` — dispatch test notification (admin only)
- Consumes: `User::telegram_verification_token`, `User::telegram_chat_id`

- [ ] **Step 1: Write failing tests**

```php
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
```

- [ ] **Step 2: Run to verify fail**

Run: `vendor/bin/pest tests/Feature/TelegramProfileTest.php -v`
Expected: FAIL — routes/controller methods belum ada

- [ ] **Step 3: Add methods to ProfileController**

```php
use Illuminate\Support\Str;
use App\Jobs\SendTestTelegram;

// ... di dalam class, setelah method updatePassword()

public function linkTelegram(Request $request)
{
    $user = $request->user();

    if ($user->telegram_verification_token) {
        $token = $user->telegram_verification_token;
    } else {
        $token = (string) Str::uuid();
        $user->update(['telegram_verification_token' => $token]);
    }

    $botName = config('services.telegram.bot_name', 'inv_lab_bot');
    $url = "https://t.me/{$botName}?start={$token}";

    return response()->json([
        'success' => true,
        'data' => [
            'url' => $url,
            'token' => $token,
        ],
    ]);
}

public function unlinkTelegram(Request $request)
{
    $request->user()->update([
        'telegram_chat_id' => null,
        'telegram_verification_token' => null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Telegram berhasil diputuskan.',
    ]);
}

public function testTelegram(Request $request)
{
    $user = $request->user();

    if (! $user->isAdmin()) {
        abort(403, 'Hanya admin yang dapat mengirim pesan uji coba.');
    }

    SendTestTelegram::dispatch();

    return response()->json([
        'success' => true,
        'message' => 'Pesan uji coba sedang dikirim ke semua pengguna yang terhubung.',
    ]);
}
```

- [ ] **Step 4: Add routes**

Di `routes/web.php` di dalam group `middleware('auth')` setelah route profile yang sudah ada:
```php
// Telegram Linking
Route::post('/profile/link-telegram', [ProfileController::class, 'linkTelegram'])->name('profile.link-telegram');
Route::post('/profile/unlink-telegram', [ProfileController::class, 'unlinkTelegram'])->name('profile.unlink-telegram');
Route::post('/profile/test-telegram', [ProfileController::class, 'testTelegram'])->name('profile.test-telegram');
```

- [ ] **Step 5: Run tests**

Run: `vendor/bin/pest tests/Feature/TelegramProfileTest.php -v`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/ProfileController.php routes/web.php tests/Feature/TelegramProfileTest.php
git commit -m "feat: add telegram link/unlink/test profile endpoints"
```

---

### Task 6: Queue Jobs & Scheduler

**Files:**
- Create: `app/Jobs/SendDailyPatrolReminders.php`
- Create: `app/Jobs/SendTestTelegram.php`
- Modify: `routes/console.php`
- Test: `tests/Feature/TelegramJobsTest.php`

**Interfaces:**
- Produces: `App\Jobs\SendDailyPatrolReminders` — dispatch notifikasi ke asisten + admin
- Produces: `App\Jobs\SendTestTelegram` — kirim test ke semua user dengan chat_id
- Consumes: `PatrolReminderNotification` dari Task 3, `TestTelegramNotification` dari Task 3

- [ ] **Step 1: Write failing tests**

```php
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
        'day_of_week' => strtolower(now()->format('l')),
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
        'day_of_week' => strtolower(now()->format('l')),
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
        'day_of_week' => strtolower(now()->format('l')),
        'status' => 'active',
    ]);

    SendDailyPatrolReminders::dispatchSync();

    Notification::assertSentTo($admin, PatrolReminderNotification::class);
});

it('test telegram sends to all linked users', function () {
    Notification::fake();

    User::factory()->create(['telegram_chat_id' => '111']);
    User::factory()->create(['telegram_chat_id' => '222']);
    User::factory()->create(['telegram_chat_id' => null]); // should not get

    SendTestTelegram::dispatchSync();

    expect(Notification::sentNotifications()->count())->toBe(2);
});
```

- [ ] **Step 2: Run to verify fail**

Run: `vendor/bin/pest tests/Feature/TelegramJobsTest.php -v`
Expected: FAIL — Job classes not found

- [ ] **Step 3: Create SendDailyPatrolReminders job**

```php
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
        $today = strtolower(now()->format('l'));

        // Kirim ke asisten (per individu)
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

        // Kirim laporan ke admin
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
```

- [ ] **Step 4: Create SendTestTelegram job**

```php
<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\TestTelegramNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class SendTestTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $users = User::whereNotNull('telegram_chat_id')->get();

        foreach ($users as $user) {
            $user->notify(new TestTelegramNotification);
        }
    }
}
```

- [ ] **Step 5: Add scheduler to console.php**

```php
use App\Jobs\SendDailyPatrolReminders;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new SendDailyPatrolReminders)
    ->dailyAt('23:00')
    ->timezone('UTC')
    ->name('send-daily-patrol-reminders');
```

- [ ] **Step 6: Run tests**

Run: `vendor/bin/pest tests/Feature/TelegramJobsTest.php -v`
Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add app/Jobs/SendDailyPatrolReminders.php app/Jobs/SendTestTelegram.php routes/console.php tests/Feature/TelegramJobsTest.php
git commit -m "feat: add telegram queue jobs and scheduler"
```

---

### Task 7: Profile View UI

**Files:**
- Modify: `resources/views/profile/show.blade.php`

**Interfaces:**
- Consumes: `auth()->user()->hasTelegramLinked()`, `auth()->user()->isAdmin()`
- Consumes: `POST /profile/link-telegram`, `/profile/unlink-telegram`, `/profile/test-telegram` dari Task 5

- [ ] **Step 1: Read existing view for context** (sudah dibaca)

Lihat struktur existing `resources/views/profile/show.blade.php` — ada profile card (kolom kiri) dan form (kolom kanan).

- [ ] **Step 2: Add Telegram section to view**

Tambahkan setelah form Change Password (sebelum `@push('scripts')`):

```blade
{{-- Telegram Integration --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <h2 class="font-semibold text-slate-700 mb-4">🔗 Telegram</h2>

    @if($user->hasTelegramLinked())
        <div class="flex items-center gap-3 mb-4">
            <span class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full font-semibold bg-green-50 text-green-700">
                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                Terhubung ke Telegram
            </span>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="button" id="unlink-telegram"
                class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-100 rounded-xl hover:bg-red-100 transition-colors">
                Putuskan
            </button>

            @if($user->isAdmin())
                <button type="button" id="test-telegram"
                    class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-100 rounded-xl hover:bg-blue-100 transition-colors">
                    📨 Kirim Pesan Uji Coba
                </button>
            @endif
        </div>
    @else
        <p class="text-sm text-slate-500 mb-4">
            Hubungkan akun Telegram Anda untuk menerima notifikasi patrol secara otomatis.
        </p>
        <button type="button" id="link-telegram"
            class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all">
            🔗 Hubungkan ke Telegram
        </button>
    @endif
</div>
```

- [ ] **Step 3: Add JavaScript handlers**

Tambahkan di dalam `@push('scripts')` setelah handler password form:

```javascript
// Telegram Link
$('#link-telegram').on('click', function() {
    $.ajax({
        url: '/profile/link-telegram',
        type: 'POST',
        success: function(res) {
            window.open(res.data.url, '_blank');
            window.showAlert('Berhasil!', 'Token telah dibuat. Klik link di tab baru untuk menghubungkan Telegram.', 'success');
        },
        error: function(err) {
            Swal.fire('Error', err.responseJSON?.message || 'Gagal membuat token.', 'error');
        }
    });
});

// Telegram Unlink
$('#unlink-telegram').on('click', function() {
    Swal.fire({
        title: 'Putuskan Telegram?',
        text: 'Anda tidak akan menerima notifikasi lagi.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, putuskan',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/profile/unlink-telegram',
                type: 'POST',
                success: function(res) {
                    window.showAlert('Berhasil!', res.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                },
                error: function(err) {
                    Swal.fire('Error', err.responseJSON?.message || 'Gagal memutuskan.', 'error');
                }
            });
        }
    });
});

// Test Telegram (Admin only)
$('#test-telegram').on('click', function() {
    $.ajax({
        url: '/profile/test-telegram',
        type: 'POST',
        success: function(res) {
            window.showAlert('Berhasil!', res.message, 'success');
        },
        error: function(err) {
            Swal.fire('Error', err.responseJSON?.message || 'Gagal mengirim pesan uji coba.', 'error');
        }
    });
});
```

- [ ] **Step 4: Set webhook URL di BotFather**

Setelah deploy, jalankan perintah ini di browser atau terminal:

```bash
curl -F "url=https://domain-anda.com/api/telegram/webhook" "https://api.telegram.org/bot8535256556:AAHHjRU9Pqvclvab2-rt7M34NwW7IiLOYi4/setWebhook"
```

Atau buka URL: `https://api.telegram.org/bot8535256556:AAHHjRU9Pqvclvab2-rt7M34NwW7IiLOYi4/setWebhook?url=https://domain-anda.com/api/telegram/webhook`

- [ ] **Step 5: Visual check**

Run: `php artisan serve` lalu buka `/profile`
Verify tombol "Hubungkan ke Telegram" muncul. Jika sudah terhubung, verifikasi badge "Terhubung" + tombol "Putuskan".

- [ ] **Step 6: Run all tests**

Run: `vendor/bin/pest tests/Feature/TelegramMigrationTest.php tests/Feature/TelegramChannelTest.php tests/Feature/TelegramNotificationTest.php tests/Feature/TelegramWebhookTest.php tests/Feature/TelegramProfileTest.php tests/Feature/TelegramJobsTest.php -v`

Expected: ALL PASS

- [ ] **Step 7: Commit**

```bash
git add resources/views/profile/show.blade.php
git commit -m "feat: add telegram linking UI to profile page"
```

---

## Post-Implementation Checklist

- [ ] Set webhook URL di BotFather setelah deploy
- [ ] Test flow "Hubungkan ke Telegram" dari profil sampai dapat notifikasi
- [ ] Test notifikasi 06:00 WIB (bisa test manual dengan `php artisan schedule:run` atau langsung dispatch job)
- [ ] Test tombol "Kirim Pesan Uji Coba" untuk admin
- [ ] Verifikasi log tidak ada error untuk user tanpa telegram_chat_id
- [ ] Jika ada error, cek `storage/logs/laravel.log`
