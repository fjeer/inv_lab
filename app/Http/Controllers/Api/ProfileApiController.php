<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileApiController extends BaseApiController
{
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        ActivityLog::log(
            'update_profile',
            "Memperbarui profil pengguna: {$user->name}",
            $user,
        );

        return $this->sendSuccess(
            UserResource::make($user),
            'Profil berhasil diperbarui.',
        );
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated()['password']),
        ]);

        ActivityLog::log(
            'change_password',
            "Mengubah password pengguna: {$request->user()->name}",
        );

        return $this->sendSuccess(null, 'Password berhasil diubah.');
    }
}
