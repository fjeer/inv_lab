<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementItem extends Model
{
    protected $fillable = [
        'procurement_id',
        'item_name',
        'specification',
        'quantity',
        'unit',
        'estimated_price',
        'subtotal',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    /* ---- Relationships ---- */

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }
}
