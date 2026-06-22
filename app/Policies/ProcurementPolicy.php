<?php

namespace App\Policies;

use App\Models\Procurement;
use App\Models\User;

class ProcurementPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Procurement $procurement): bool
    {
        return $user->isAdmin() || $user->id === $procurement->requested_by;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function approve(User $user, Procurement $procurement): bool
    {
        return $user->isAdmin();
    }

    public function reject(User $user, Procurement $procurement): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Procurement $procurement): bool
    {
        return $user->isAdmin();
    }
}
