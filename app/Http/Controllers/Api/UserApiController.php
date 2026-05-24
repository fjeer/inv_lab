<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = User::query();

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
            $page = ($start / $limit) + 1;
            $users = $query->orderBy('name')->paginate($limit, ['*'], 'page', $page);
            return $this->sendPaginated($users, 'Data pengguna dikumpulkan');
        }

        $users = $query->orderBy('name')->get();
        return $this->sendSuccess($users, 'Data pengguna dikumpulkan');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return $this->sendSuccess($user, 'Detail ditemukan');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin_lab,asisten_lab,pengguna',
            'nim_nip' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'nim_nip' => $request->nim_nip,
            'phone' => $request->phone,
            'department' => $request->department,
            'is_active' => true,
        ]);

        return $this->sendSuccess($user, 'Pengguna berhasil dibuat', 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin_lab,asisten_lab,pengguna',
            'nim_nip' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $user->update($request->only('name', 'email', 'role', 'nim_nip', 'phone', 'department', 'is_active'));

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return $this->sendSuccess($user, 'Pengguna berhasil diperbarui');
    }

    public function destroy($id)
    {
        User::destroy($id);
        return $this->sendSuccess(null, 'Pengguna berhasil dihapus');
    }
}
