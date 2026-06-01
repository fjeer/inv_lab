<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;
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
