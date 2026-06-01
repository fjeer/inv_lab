<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = User::query();
        $this->applyTrashedFilter($query, $request);

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // DataTables search
        $search = null;
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
        } elseif ($request->filled('search') && !is_array($request->input('search'))) {
            $search = $request->input('search');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('nim_nip', 'like', "%$search%");
            });
        }

        if ($request->has('length')) {
            $limit = $request->input('length', 10);
            $start = $request->input('start', 0);
            $limit = max($limit, 1); $page = (int)($start / $limit) + 1;
            $users = $query->orderBy('name')->paginate($limit, ['*'], 'page', $page);
            return $this->sendPaginated($users, 'Data pengguna dikumpulkan');
        }

        $users = $query->orderBy('name')->get();
        return $this->sendSuccess($users, 'Data pengguna dikumpulkan');
    }

    public function show($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        return $this->sendSuccess($user, 'Detail ditemukan');
    }

    public function store(StoreUserRequest $request)
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

        return $this->sendSuccess($user, 'Pengguna berhasil dibuat', 201);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);
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

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return $this->sendSuccess($user, 'Pengguna berhasil diperbarui');
    }

    public function destroy($id)
    {
        User::destroy($id);
        return $this->sendSuccess(null, 'Pengguna berhasil dihapus');
    }
}
