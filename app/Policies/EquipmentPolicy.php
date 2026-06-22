<?php

namespace App\Policies;

use App\Models\Equipment;
use App\Models\User;

class EquipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin', 'asisten');
    }

    public function view(User $user, Equipment $equipment): bool
    {
        return $user->hasRole('admin', 'asisten');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin', 'asisten');
    }

    public function update(User $user, Equipment $equipment): bool
    {
        return $user->hasRole('admin', 'asisten');
    }

    public function delete(User $user, Equipment $equipment): bool
    {
        return $user->hasRole('admin', 'asisten');
    }

    public function restore(User $user, Equipment $equipment): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Equipment $equipment): bool
    {
        return $user->hasRole('admin');
    }
}
