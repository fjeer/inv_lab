<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laboratory extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'code',
        'room_id',
        'location',
        'capacity',
        'description',
        'responsible_person_id',
        'status',
    ];

    /* ---- Relationships ---- */

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_person_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'laboratory_id');
    }


    public function patrolSchedules(): HasMany
    {
        return $this->hasMany(PatrolSchedule::class);
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(LabBorrowing::class);
    }

    /**
     * Get all equipment items through equipment in this lab.
     */
    public function equipmentItems(): HasManyThrough
    {
        return $this->hasManyThrough(EquipmentItem::class, Equipment::class);
    }

    /* ---- Scopes ---- */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
