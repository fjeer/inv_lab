<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();
        $this->applyTrashedFilter($query, $request);

        if ($request->filled('role')) {
            $query->where(function ($q) use ($request) {
                $q->where('role', $request->role)
                  ->orWhereHas('roleRelation', fn ($r) => $r->where('name', $request->role));
            });
        }

        $search = $this->getSearch($request);

        if ($search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nim_nip', 'like', "%{$search}%");
            });
        }

        $perPage = $this->getPerPage($request);
        $page = $this->getPageFromRequest($request);

        $users = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        return $this->sendPaginated($users, 'Data pengguna berhasil dimuat');
    }

    public function show(User $user): JsonResponse
    {
        return $this->sendSuccess(
            UserResource::make($user),
            'Detail ditemukan',
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'nim_nip' => $validated['nim_nip'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'is_active' => true,
        ]);

        return $this->sendSuccess(
            UserResource::make($user),
            'Pengguna berhasil dibuat',
            201,
        );
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'nim_nip' => $validated['nim_nip'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'is_active' => $validated['is_active'] ?? $user->is_active,
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return $this->sendSuccess(
            UserResource::make($user),
            'Pengguna berhasil diperbarui',
        );
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return $this->sendSuccess(null, 'Pengguna berhasil dihapus');
    }
}
