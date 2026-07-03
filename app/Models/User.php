<?php

namespace App\Models;

use App\Models\PatrolSchedule;
use App\Notifications\PatrolReminderNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'role_id', 'nim_nip', 'phone', 'department', 'avatar', 'is_active', 'telegram_chat_id', 'telegram_verification_token'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /* ---- Role Relationship (Dynamic RBAC) ---- */

    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /* ---- Role helpers (backward compatible + dynamic) ---- */

    public function isAdmin(): bool
    {
        if ($this->role_id && $this->roleRelation) {
            return $this->roleRelation->name === 'admin';
        }

        return $this->role === 'admin_lab';
    }

    public function isAsisten(): bool
    {
        if ($this->role_id && $this->roleRelation) {
            return $this->roleRelation->name === 'asisten';
        }

        return $this->role === 'asisten_lab';
    }

    public function isPengguna(): bool
    {
        if ($this->role_id && $this->roleRelation) {
            return $this->roleRelation->name === 'user';
        }

        return $this->role === 'pengguna';
    }

    public function hasRole(string ...$roles): bool
    {
        // Check dynamic role first
        if ($this->role_id && $this->roleRelation) {
            return in_array($this->roleRelation->name, $roles, true);
        }

        // Fallback to enum column
        return in_array($this->role, $roles, true);
    }

    /**
     * Check if user has a specific permission via their role.
     */
    public function hasPermission(string $permissionName): bool
    {
        if (! $this->role_id || ! $this->roleRelation) {
            // Admin fallback always has all permissions
            return $this->role === 'admin_lab';
        }

        return $this->roleRelation->hasPermission($permissionName);
    }

    public function getRoleLabelAttribute(): string
    {
        if ($this->role_id && $this->roleRelation) {
            return $this->roleRelation->display_name;
        }

        return match ($this->role) {
            'admin_lab' => 'Admin Lab',
            'asisten_lab' => 'Asisten Lab',
            'pengguna' => 'Pengguna',
            default => $this->role,
        };
    }

    public function hasTelegramLinked(): bool
    {
        return ! is_null($this->telegram_chat_id);
    }

    public function sendTodayReminder(): void
    {
        $today = strtolower(now('Asia/Jakarta')->format('l'));

        $schedules = $this->patrolSchedules()
            ->where('status', 'active')
            ->where('day_of_week', $today)
            ->with('laboratory')
            ->get();

        if ($schedules->isNotEmpty()) {
            $this->notify(new PatrolReminderNotification(
                schedules: $schedules,
                type: 'asisten'
            ));
        }

        if ($this->isAdmin()) {
            $allTodaySchedules = PatrolSchedule::with(['user', 'laboratory'])
                ->where('status', 'active')
                ->where('day_of_week', $today)
                ->get();

            if ($allTodaySchedules->isNotEmpty()) {
                $this->notify(new PatrolReminderNotification(
                    schedules: $allTodaySchedules,
                    type: 'admin'
                ));
            }
        }
    }

    /* ---- Relationships ---- */

    public function patrolSchedules(): HasMany
    {
        return $this->hasMany(PatrolSchedule::class);
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(LabBorrowing::class);
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'reported_by');
    }

    public function procurements(): HasMany
    {
        return $this->hasMany(Procurement::class, 'requested_by');
    }

    public function conditionChecks(): HasMany
    {
        return $this->hasMany(EquipmentCondition::class, 'checked_by');
    }
}
