<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamageReport extends Model
{
    protected $fillable = [
        'equipment_id',
        'reported_by',
        'handled_by',
        'damage_type',
        'description',
        'incident_date',
        'photo',
        'status',
        'repair_cost',
        'repair_notes',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'resolved_at' => 'datetime',
            'repair_cost' => 'decimal:2',
        ];
    }

    /* ---- Relationships ---- */

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /* ---- Accessors ---- */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'reported' => 'Dilaporkan',
            'in_review' => 'Ditinjau',
            'in_repair' => 'Diperbaiki',
            'repaired' => 'Selesai Perbaikan',
            'unrepairable' => 'Tidak Bisa Diperbaiki',
            'closed' => 'Ditutup',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'reported' => 'yellow',
            'in_review' => 'blue',
            'in_repair' => 'indigo',
            'repaired' => 'green',
            'unrepairable' => 'red',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    public function getDamageTypeLabelAttribute(): string
    {
        return match ($this->damage_type) {
            'ringan' => 'Ringan',
            'sedang' => 'Sedang',
            'berat' => 'Berat',
            default => $this->damage_type,
        };
    }
}
