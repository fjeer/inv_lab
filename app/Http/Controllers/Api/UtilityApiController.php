<?php

namespace App\Http\Controllers\Api;

use App\Models\EquipmentCategory;
use App\Models\Laboratory;
use Illuminate\Http\Request;

class UtilityApiController extends BaseApiController
{
    /**
     * Get options for dropdowns.
     */
    public function options()
    {
        $assistants = \App\Models\User::where(function ($q) {
            $q->where('role', 'asisten_lab')
              ->orWhereHas('roleRelation', fn ($r) => $r->where('name', 'asisten'));
        })->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return $this->sendSuccess([
            'laboratories' => Laboratory::select('id', 'name')->orderBy('name')->get(),
            'categories' => EquipmentCategory::select('id', 'name')->orderBy('name')->get(),
            'assistants' => $assistants,
        ], 'Options loaded');
    }
}
