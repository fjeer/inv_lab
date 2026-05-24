<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabSchedule extends Model
{
    protected $fillable = [
        'laboratory_id',
        'user_id',
        'title',
        'day_of_week',
        'start_time',
        'end_time',
        'semester',
        'academic_year',
        'class_group',
        'status',
        'notes',
    ];

    /* ---- Relationships ---- */

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
