<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = [
        'laboratory_id',
        'category_id',
        'name',
        'code',
        'brand',
        'model',
        'serial_number',
        'year_acquired',
        'price',
        'quantity',
        'condition',
        'status',
        'photo',
        'description',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'year_acquired' => 'integer',
        ];
    }

    /* ---- Relationships ---- */

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(EquipmentCondition::class);
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class);
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

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available' => 'Tersedia',
            'in_use' => 'Digunakan',
            'borrowed' => 'Dipinjam',
            'maintenance' => 'Maintenance',
            'disposed' => 'Dihapuskan',
            default => $this->status,
        };
    }

    /* ---- Scopes ---- */

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByCondition($query, string $condition)
    {
        return $query->where('condition', $condition);
    }
}
