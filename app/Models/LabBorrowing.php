<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabBorrowing extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'laboratory_id',
        'user_id',
        'approved_by',
        'purpose',
        'activity_type',
        'borrow_date',
        'start_time',
        'end_time',
        'status',
        'rejection_reason',
        'notes',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'borrow_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    /* ---- Relationships ---- */

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /* ---- Accessors ---- */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'ongoing' => 'Berlangsung',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'approved' => 'blue',
            'rejected' => 'red',
            'ongoing' => 'indigo',
            'completed' => 'green',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }
}
