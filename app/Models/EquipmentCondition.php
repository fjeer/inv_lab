<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentCondition extends Model
{
    protected $fillable = [
        'equipment_id',
        'checked_by',
        'condition',
        'previous_condition',
        'check_date',
        'description',
        'action_taken',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'check_date' => 'date',
        ];
    }

    /* ---- Relationships ---- */

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    /* ---- Accessors ---- */

    public function getConditionLabelAttribute(): string
    {
        return match ($this->condition) {
            'baik' => 'Baik',
            'rusak_ringan' => 'Rusak Ringan',
            'rusak_berat' => 'Rusak Berat',
            'hilang' => 'Hilang',
            default => $this->condition,
        };
    }
}
