<?php

namespace App\Models;

use App\Models\Concerns\HasConditionLabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentCondition extends Model
{
    use HasConditionLabel;
    use SoftDeletes;
    protected $fillable = [
        'equipment_id',
        'equipment_item_id',
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

    public function equipmentItem(): BelongsTo
    {
        return $this->belongsTo(EquipmentItem::class, 'equipment_item_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    /* ---- Accessors ---- */

}
