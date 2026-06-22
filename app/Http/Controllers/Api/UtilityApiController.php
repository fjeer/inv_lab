<?php

namespace App\Http\Controllers\Api;

use App\Models\EquipmentCategory;
use App\Models\Laboratory;
use App\Models\User;

class UtilityApiController extends BaseApiController
{
    public function options()
    {
        $assistants = User::whereHas('roleRelation', fn ($r) => $r->where('name', 'asisten'))
            ->orWhere('role', 'asisten_lab')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return $this->sendSuccess([
            'laboratories' => Laboratory::select('id', 'name')->orderBy('name')->get(),
            'categories' => EquipmentCategory::select('id', 'name')->orderBy('name')->get(),
            'assistants' => $assistants,
        ], 'Options loaded');
    }
}
