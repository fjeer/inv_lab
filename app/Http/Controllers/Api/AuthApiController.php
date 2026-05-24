<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthApiController extends BaseApiController
{
    /**
     * API Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return $this->sendError('Akun Anda telah dinonaktifkan.', [], 403);
            }

            // Create token for API consumers
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->sendSuccess([
                'token_type' => 'Bearer',
                'access_token' => $token,
                'user' => $user,
                'redirect' => route('dashboard')
            ], 'Login berhasil');
        }

        return $this->sendError('Email atau password salah.', [], 401);
    }

    /**
     * API Register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'nim_nip' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'department' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'pengguna',
            'nim_nip' => $validated['nim_nip'],
            'phone' => $validated['phone'],
            'department' => $validated['department'],
            'is_active' => true,
        ]);

        Auth::login($user);
        /** @var \App\Models\User $user */
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->sendSuccess([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'redirect' => route('dashboard')
        ], 'Registrasi berhasil', 201);
    }

    /**
     * API Logout
     */
    public function logout(Request $request)
    {
        try {
            // 1. Revoke the specific token (only if it's a real PersonalAccessToken, not a session/transient token)
            $token = $request->user() ? $request->user()->currentAccessToken() : null;
            if ($token && method_exists($token, 'delete')) {
                $token->delete();
            }

            // 2. Logout from web session guard
            Auth::guard('web')->logout();

            // 3. Clear session data safely
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return $this->sendSuccess(null, 'Logout berhasil');
        } catch (\Exception $e) {
            // Fallback for unexpected errors to avoid 500
            Auth::guard('web')->logout();
            return $this->sendSuccess(null, 'Logout dipaksakan berhasil');
        }
    }
}
