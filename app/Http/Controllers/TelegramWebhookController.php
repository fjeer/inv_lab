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
