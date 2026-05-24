<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratory extends Model
{
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

    public function responsiblePerson()
    {
        return $this->belongsTo(User::class, 'responsible_person_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'laboratory_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(LabSchedule::class);
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(LabBorrowing::class);
    }

    /* ---- Scopes ---- */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
