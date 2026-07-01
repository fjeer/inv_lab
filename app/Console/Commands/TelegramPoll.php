<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramPoll extends Command
{
    protected $signature = 'telegram:poll {--reset-offset : Reset offset untuk ambil ulang semua pesan}';

    protected $description = 'Ambil pending updates dari Telegram bot dan proses verifikasi token';

    public function handle(): int
    {
        $botToken = config('services.telegram.bot_token');

        if (! $botToken || $botToken === 'test-token') {
            $this->error('TELEGRAM_BOT_TOKEN belum diatur di .env');
            return Command::FAILURE;
        }

        $offset = $this->option('reset-offset') ? null : cache('telegram_last_update_id');

        $response = Http::post("https://api.telegram.org/bot{$botToken}/getUpdates", array_filter([
            'offset' => $offset,
            'timeout' => 10,
        ]));

        if (! $response->successful() || ! ($response['ok'] ?? false)) {
            $this->error('Gagal getUpdates: ' . $response->body());
            return Command::FAILURE;
        }

        $updates = $response['result'] ?? [];
        $processed = 0;

        foreach ($updates as $update) {
            $updateId = $update['update_id'];
            $message = $update['message'] ?? [];
            $chatId = $message['chat']['id'] ?? null;
            $text = $message['text'] ?? '';

            cache(['telegram_last_update_id' => $updateId + 1]);

            if (! $chatId || ! str_starts_with($text, '/start ')) {
                $this->line("  ↳ skip: {$text}");
                continue;
            }

            $token = trim(substr($text, 7));
            $user = User::where('telegram_verification_token', $token)->first();

            if (! $user) {
                Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => "❌ Token tidak valid atau sudah kedaluwarsa.\nSilakan coba lagi dari aplikasi.",
                ]);
                $this->warn("  ✗ Token invalid: {$token}");
                continue;
            }

            $user->update([
                'telegram_chat_id' => $chatId,
                'telegram_verification_token' => null,
            ]);

            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => "✅ Akun Telegram berhasil dihubungkan ke {$user->name}!",
            ]);

            $this->info("  ✓ {$user->name} terhubung (chat_id: {$chatId})");
            $processed++;
        }

        $total = count($updates);
        $this->line("Selesai: {$total} update(s), {$processed} linking(s)");

        return Command::SUCCESS;
    }
}
