<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Jobs\SendTestTelegram;

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

        // Manual chat_id input (admin bypass for localhost)
        if ($manualChatId = $request->input('manual_chat_id')) {
            $user->update([
                'telegram_chat_id' => $manualChatId,
                'telegram_verification_token' => null,
            ]);

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
