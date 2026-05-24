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
        return $this->sendSuccess([
            'laboratories' => Laboratory::select('id', 'name')->orderBy('name')->get(),
            'categories' => EquipmentCategory::select('id', 'name')->orderBy('name')->get(),
        ], 'Options loaded');
    }
}
