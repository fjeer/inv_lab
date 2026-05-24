<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentCondition;
use App\Models\Laboratory;
use Illuminate\Http\Request;

class EquipmentConditionController extends Controller
{
    public function index(Request $request)
    {
        $query = EquipmentCondition::with(['equipment.laboratory', 'checker']);

        if ($request->filled('laboratory_id')) {
            $query->whereHas('equipment', function ($q) use ($request) {
                $q->where('laboratory_id', $request->laboratory_id);
            });
        }

        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        $conditions = $query->orderByDesc('check_date')->paginate(15)->withQueryString();
        $laboratories = Laboratory::orderBy('name')->get();
        $equipmentList = Equipment::orderBy('name')->get();

        return view('conditions.index', compact('conditions', 'laboratories', 'equipmentList'));
    }

    public function create()
    {
        $laboratories = Laboratory::orderBy('name')->get();
        $equipmentList = Equipment::with('laboratory')->orderBy('name')->get();

        return view('conditions.create', compact('laboratories', 'equipmentList'));
    }

    public function show(EquipmentCondition $condition)
    {
        $condition->load(['equipment.laboratory', 'checker']);

        return view('conditions.show', compact('condition'));
    }
}
