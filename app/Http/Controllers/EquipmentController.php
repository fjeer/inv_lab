<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Laboratory;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipment::with(['laboratory', 'category']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('brand', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $equipment = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $laboratories = Laboratory::orderBy('name')->get();
        $categories = EquipmentCategory::orderBy('name')->get();

        return view('equipment.index', compact('equipment', 'laboratories', 'categories'));
    }

    public function show(Equipment $equipment)
    {
        $equipment->load(['laboratory', 'category', 'conditions.checker', 'damageReports.reporter']);

        return view('equipment.show', compact('equipment'));
    }
}
