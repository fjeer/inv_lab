<?php

namespace App\Policies;

use App\Models\DamageReport;
use App\Models\User;

class DamageReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DamageReport $report): bool
    {
        return $user->hasRole('admin', 'asisten') || $user->id === $report->reported_by;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function updateStatus(User $user, DamageReport $report): bool
    {
        return $user->hasRole('admin', 'asisten');
    }

    public function delete(User $user, DamageReport $report): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, DamageReport $report): bool
    {
        return $user->isAdmin();
    }
}
