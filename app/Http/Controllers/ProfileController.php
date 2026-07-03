<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Jobs\SendTestTelegram;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'nim_nip' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'department' => ['nullable', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return redirect()->route('profile.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Password berhasil diubah.');
    }

    public function linkTelegram(Request $request)
    {
        $user = $request->user();

        // Manual chat_id input
        if ($manualChatId = $request->input('manual_chat_id')) {
            $existing = User::where('telegram_chat_id', $manualChatId)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat ID ini sudah terhubung ke akun lain. Setiap akun harus punya Chat ID unik.',
                ], 422);
            }

            $user->update([
                'telegram_chat_id' => $manualChatId,
                'telegram_verification_token' => null,
            ]);

            $user->sendTodayReminder();

            return response()->json([
                'success' => true,
                'message' => 'Telegram berhasil dihubungkan secara manual.',
            ]);
        }

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

    public function checkTelegramLink(Request $request)
    {
        $user = $request->user();
        $token = $user->telegram_verification_token;

        if (! $token || $user->telegram_chat_id) {
            return response()->json([
                'success' => true,
                'linked' => (bool) $user->telegram_chat_id,
            ]);
        }

        $botToken = config('services.telegram.bot_token');

        if (! $botToken || $botToken === 'test-token') {
            return response()->json([
                'success' => false,
                'linked' => false,
                'message' => 'Bot token belum dikonfigurasi.',
            ]);
        }

        $offset = cache('telegram_last_update_id');
        $response = Http::post("https://api.telegram.org/bot{$botToken}/getUpdates", array_filter([
            'offset' => $offset,
            'timeout' => 5,
        ]));

        if (! $response->successful() || ! ($response['ok'] ?? false)) {
            return response()->json([
                'success' => false,
                'linked' => false,
                'message' => 'Gagal memeriksa Telegram.',
            ]);
        }

        foreach ($response['result'] ?? [] as $update) {
            $updateId = $update['update_id'];
            $message = $update['message'] ?? [];
            $chatId = $message['chat']['id'] ?? null;
            $text = $message['text'] ?? '';

            cache(['telegram_last_update_id' => $updateId + 1]);

            if (! $chatId || ! str_starts_with($text, '/start ')) {
                continue;
            }

            $msgToken = trim(substr($text, 7));

            if ($msgToken === $token) {
                $existing = User::where('telegram_chat_id', $chatId)
                    ->where('id', '!=', $user->id)
                    ->exists();

                if ($existing) {
                    Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => "❌ Chat ID ini sudah terhubung ke akun lain.",
                    ]);

                    return response()->json([
                        'success' => false,
                        'linked' => false,
                        'message' => 'Chat ID sudah dipakai akun lain.',
                    ]);
                }

                $user->update([
                    'telegram_chat_id' => $chatId,
                    'telegram_verification_token' => null,
                ]);

                $user->sendTodayReminder();

                Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => "✅ Akun Telegram berhasil dihubungkan ke {$user->name}!",
                ]);

                return response()->json([
                    'success' => true,
                    'linked' => true,
                    'message' => 'Telegram berhasil dihubungkan!',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'linked' => false,
            'message' => 'Menunggu konfirmasi dari Telegram...',
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
}
