# Telegram Notification — Design Document

**Date:** 2026-07-01
**Status:** Approved

## 1. Tujuan

Mengirim notifikasi Telegram otomatis setiap hari pukul 06:00 WIB:
- **Asisten** mendapat pengingat patrol yang dijadwalkan hari itu
- **Admin** mendapat laporan daftar semua asisten yang bertugas

## 2. Data Layer

### Migration — `add_telegram_fields_to_users_table`

Tambah dua kolom ke tabel `users`:

| Kolom | Tipe | Constraint | Keterangan |
|-------|------|-----------|------------|
| `telegram_chat_id` | string | nullable, unique | Chat ID dari Telegram setelah linking |
| `telegram_verification_token` | string | nullable, unique | Token sekali pakai untuk verifikasi linking |

### Config

`config/services.php`:
```php
'telegram' => [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
],
```

`.env`:
```
TELEGRAM_BOT_TOKEN=8535256556:AAHHjRU9Pqvclvab2-rt7M34NwW7IiLOYi4
```

## 3. Bot Verification Flow

### Flow "Hubungkan ke Telegram"

1. User klik tombol "Hubungkan ke Telegram" di halaman profil
2. Backend generate UUID sebagai `telegram_verification_token`, simpan ke user
3. Redirect user ke `https://t.me/{nama_bot}?start={token}`
4. Bot kirim perintah `/start {token}` ke webhook endpoint
5. **Webhook endpoint** `POST /api/telegram/webhook`:
   - Parse `chat_id` dan `token` dari pesan Telegram
   - Cari user berdasarkan `telegram_verification_token`
   - Jika cocok → simpan `chat_id`, hapus token, balas "✅ Berhasil dihubungkan"
   - Jika tidak cocok → balas "❌ Token tidak valid"
6. Set webhook via BotFather: `https://domain.com/api/telegram/webhook`

### Unlink
- Tombol "Putuskan" → hapus `telegram_chat_id` dari user

## 4. Notifikasi Delivery

### Custom Notification Channel: `App/Channels/TelegramChannel`

- `send($notifiable, Notification $notification)`
- Panggil `Http::post("https://api.telegram.org/bot{token}/sendMessage", [...])`
- Format: `parse_mode => 'HTML'`, `chat_id`, `text`

### Notification Class: `App/Notifications/PatrolReminderNotification`

- `via()` → return `[TelegramChannel::class]`
- `toTelegram($notifiable)` → return array pesan berformat
- Constructor menerima `Collection $schedules` dan `string $type` ('asisten' | 'admin')

### Queue Job: `App/Jobs/SendDailyPatrolReminders`

Logic:
```php
$asistens = User::whereHas('patrolSchedules', fn($q) =>
    $q->active()->forToday()
)->whereNotNull('telegram_chat_id')->get();

foreach ($asistens as $asisten) {
    $schedules = $asisten->patrolSchedules()->active()->forToday()
        ->with('laboratory')->get();
    $asisten->notify(new PatrolReminderNotification($schedules, 'asisten'));
}

$allSchedules = PatrolSchedule::active()->forToday()
    ->with('user', 'laboratory')->get();

if ($allSchedules->isNotEmpty()) {
    User::role('admin')->whereNotNull('telegram_chat_id')
        ->each(fn($a) => $a->notify(
            new PatrolReminderNotification($allSchedules, 'admin')
        ));
}
```

### Scheduler `routes/console.php`

```php
Schedule::job(new SendDailyPatrolReminders)
    ->dailyAt('23:00')     // UTC → 06:00 WIB
    ->timezone('UTC');
```

### Test Notification

- Tombol "Kirim Pesan Uji Coba" di profil admin
- `POST /profile/test-telegram`
- Dispatch `SendTestTelegramNotification` → kirim ke semua user dengan `telegram_chat_id` yang aktif
- Pesan: "🔔 Ini adalah pesan uji coba dari Sistem Patrol Inventaris Lab. Notifikasi Telegram berfungsi dengan baik! ✅"

## 5. Format Pesan

### Ke Asisten

```
🔔 PENGINGAT PATROL HARI INI

Halo {nama_asisten},

Anda memiliki jadwal patrol hari ini:

📍 {nama_lab}
⏰ {start_time} - {end_time}

Harap lakukan patrol sesuai jadwal.

Terima kasih.
─
Sistem Patrol Inventaris Lab
```

### Ke Admin

```
📋 LAPORAN PATROL HARI INI
{hari}, {tanggal}

Berikut daftar asisten yang bertugas:

1. {nama_asisten}
   📍 {nama_lab}
   ⏰ {start_time} - {end_time}

2. {nama_asisten}
   📍 {nama_lab}
   ⏰ {start_time} - {end_time}

Total: {jumlah} asisten bertugas

─
Sistem Patrol Inventaris Lab
```

## 6. Route / UI

### Web Routes

| Method | URI | Controller Method | Middleware | Keterangan |
|--------|-----|-------------------|------------|------------|
| POST | `/profile/link-telegram` | `ProfileController@linkTelegram` | auth | Generate token, return deep link |
| POST | `/profile/unlink-telegram` | `ProfileController@unlinkTelegram` | auth | Hapus chat_id |
| POST | `/profile/test-telegram` | `ProfileController@testTelegram` | auth, role:admin | Kirim test (admin only) |
| Any | `/api/telegram/webhook` | `TelegramWebhookController@handle` | none (public) | Handle incoming bot messages |

### UI Component

- **Belum terhubung:** Tombol "🔗 Hubungkan ke Telegram" + teks penjelasan
- **Terhubung:** Badge "✓ Terhubung ke Telegram" + tombol "Putuskan" + tombol "📨 Kirim Pesan Uji Coba" (admin only)

## 7. Error Handling & Edge Cases

- **Token expired** → set expiry 5 menit, tampilkan pesan "Token kedaluwarsa, coba lagi"
- **Duplicate chat_id** — migration enforce unique, catch exception saat linking
- **Bot di-block user** — `sendMessage` return `{"ok":false}` → log warning, jangan crash
- **Tidak ada jadwal hari ini** — job tetap jalan, skip, log info "No schedules today"
- **Admin belum link Telegram** — skip, tidak dikirimi
- **Queue gagal** — retry 3x via Laravel job `$tries` or `retryUntil`
