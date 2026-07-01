<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatrolSchedule extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'laboratory_id',
        'day_of_week',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];

    /* ---- Relationships ---- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function patrolLogs(): HasMany
    {
        return $this->hasMany(PatrolLog::class);
    }

    /* ---- Accessors ---- */

    public function getDayLabelAttribute(): string
    {
        return match ($this->day_of_week) {
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
            default => $this->day_of_week,
        };
    }

    /* ---- Scopes ---- */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForToday($query)
    {
        return $query->where('day_of_week', strtolower(now()->format('l')));
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
