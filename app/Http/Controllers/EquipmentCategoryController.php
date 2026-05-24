<?php

namespace App\Http\Controllers;

use App\Models\EquipmentCategory;
use Illuminate\Http\Request;

class EquipmentCategoryController extends Controller
{
    public function index()
    {
        $categories = EquipmentCategory::withCount('equipment')
            ->orderByDesc('id')
            ->paginate(10);

        return view('categories.index', compact('categories'));
    }
}
