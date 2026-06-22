<?php

namespace App\Policies;

use App\Models\LabBorrowing;
use App\Models\User;

class LabBorrowingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LabBorrowing $borrowing): bool
    {
        return $user->hasRole('admin', 'asisten') || $user->id === $borrowing->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function approve(User $user, LabBorrowing $borrowing): bool
    {
        return $user->hasRole('admin', 'asisten');
    }

    public function reject(User $user, LabBorrowing $borrowing): bool
    {
        return $user->hasRole('admin', 'asisten');
    }

    public function delete(User $user, LabBorrowing $borrowing): bool
    {
        return $user->hasRole('admin');
    }
}
