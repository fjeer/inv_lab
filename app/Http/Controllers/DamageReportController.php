<?php

namespace App\Http\Controllers;

use App\Models\DamageReport;
use App\Models\Equipment;
use Illuminate\Http\Request;

class DamageReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = DamageReport::with(['equipment.laboratory', 'reporter', 'handler']);

        if ($user->isPengguna()) {
            $query->where('reported_by', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('damage_type')) {
            $query->where('damage_type', $request->damage_type);
        }

        $reports = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('damage-reports.index', compact('reports'));
    }

    public function create()
    {
        $laboratories = \App\Models\Laboratory::orderBy('name')->get();
        $equipmentList = Equipment::with('laboratory')->orderBy('name')->get();

        return view('damage-reports.create', compact('laboratories', 'equipmentList'));
    }

    public function show(DamageReport $damageReport)
    {
        $damageReport->load(['equipment.laboratory', 'reporter', 'handler']);

        return view('damage-reports.show', compact('damageReport'));
    }
}
