<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Procurement extends Model
{
    protected $fillable = [
        'requested_by',
        'approved_by',
        'procurement_number',
        'title',
        'description',
        'priority',
        'total_estimated_cost',
        'status',
        'rejection_reason',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_estimated_cost' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    /* ---- Relationships ---- */

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProcurementItem::class);
    }

    /* ---- Accessors ---- */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Diajukan',
            'in_review' => 'Ditinjau',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'ordered' => 'Dipesan',
            'received' => 'Diterima',
            'closed' => 'Ditutup',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'submitted' => 'yellow',
            'in_review' => 'blue',
            'approved' => 'green',
            'rejected' => 'red',
            'ordered' => 'indigo',
            'received' => 'emerald',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'urgent' => 'Mendesak',
            default => $this->priority,
        };
    }

    /**
     * Generate unique procurement number.
     */
    public static function generateNumber(): string
    {
        $prefix = 'PRC-' . date('Ym');
        $lastNumber = static::where('procurement_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('procurement_number');

        if ($lastNumber) {
            $sequence = (int) substr($lastNumber, -4) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
