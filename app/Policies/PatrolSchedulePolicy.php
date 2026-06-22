<?php

namespace App\Policies;

use App\Models\PatrolSchedule;
use App\Models\User;

class PatrolSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin', 'asisten');
    }

    public function view(User $user, PatrolSchedule $schedule): bool
    {
        return $user->hasRole('admin', 'asisten');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, PatrolSchedule $schedule): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, PatrolSchedule $schedule): bool
    {
        return $user->isAdmin();
    }
}
