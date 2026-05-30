<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentItem extends Model
{
    protected $fillable = [
        'equipment_id',
        'sequence_number',
        'qr_code',
        'condition',
        'condition_notes',
        'last_checked_at',
        'last_checked_by',
    ];

    protected function casts(): array
    {
        return [
            'last_checked_at' => 'datetime',
        ];
    }

    /* ---- Relationships ---- */

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function lastChecker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_checked_by');
    }

    public function patrolLogs(): HasMany
    {
        return $this->hasMany(PatrolLog::class);
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

    public function getConditionColorAttribute(): string
    {
        return match ($this->condition) {
            'baik' => 'emerald',
            'rusak_ringan' => 'amber',
            'rusak_berat' => 'red',
            'hilang' => 'gray',
            default => 'slate',
        };
    }

    /* ---- Static Helpers ---- */

    /**
     * Generate QR code string from the equipment's lab location data.
     */
    public static function generateQrCode(Equipment $equipment, int $sequenceNumber): string
    {
        $lab = $equipment->laboratory;
        $room = $lab?->room;
        $building = $room?->building;

        $buildingCode = $building?->code ?? 'XX';
        $roomCode = $room?->code ?? 'XX';
        $labCode = $lab?->code ?? 'XX';
        $equipmentName = str_replace(' ', '_', $equipment->name);
        $seqPadded = str_pad($sequenceNumber, 3, '0', STR_PAD_LEFT);

        return "{$buildingCode} {$roomCode} {$labCode} {$equipmentName} {$seqPadded}";
    }
}
