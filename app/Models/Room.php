<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['building_id', 'name', 'code', 'floor', 'description'];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function laboratories()
    {
        return $this->hasMany(Laboratory::class);
    }
}
