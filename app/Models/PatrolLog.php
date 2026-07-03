<?php

namespace App\Models;

use App\Models\Concerns\HasConditionLabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatrolLog extends Model
{
    use HasConditionLabel;
    use SoftDeletes;
    protected $fillable = [
        'patrol_schedule_id',
        'equipment_item_id',
        'checked_by',
        'condition',
        'previous_condition',
        'notes',
        'photo',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
        ];
    }

    /* ---- Relationships ---- */

    public function patrolSchedule(): BelongsTo
    {
        return $this->belongsTo(PatrolSchedule::class);
    }

    public function equipmentItem(): BelongsTo
    {
        return $this->belongsTo(EquipmentItem::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    /* ---- Accessors ---- */

}
