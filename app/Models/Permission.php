<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'display_name',
        'group',
    ];

    /* ---- Relationships ---- */

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    /* ---- Scopes ---- */

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
