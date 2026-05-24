<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'nim_nip', 'phone', 'department', 'avatar', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

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

    /* ---- Role helpers ---- */

    public function isAdmin(): bool
    {
        return $this->role === 'admin_lab';
    }

    public function isAsisten(): bool
    {
        return $this->role === 'asisten_lab';
    }

    public function isPengguna(): bool
    {
        return $this->role === 'pengguna';
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin_lab' => 'Admin Lab',
            'asisten_lab' => 'Asisten Lab',
            'pengguna' => 'Pengguna',
            default => $this->role,
        };
    }

    /* ---- Relationships ---- */

    public function schedules(): HasMany
    {
        return $this->hasMany(LabSchedule::class);
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
