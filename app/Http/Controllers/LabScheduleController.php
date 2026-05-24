<?php

namespace App\Http\Controllers;

use App\Models\LabSchedule;
use App\Models\Laboratory;
use App\Models\User;
use Illuminate\Http\Request;

class LabScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = LabSchedule::with(['laboratory', 'user']);

        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->orderByRaw("FIELD(day_of_week, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday')")
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        $laboratories = Laboratory::orderBy('name')->get();

        return view('schedules.index', compact('schedules', 'laboratories'));
    }

    public function create()
    {
        $laboratories = Laboratory::active()->orderBy('name')->get();
        $dosens = User::where('role', 'pengguna')->orderBy('name')->get();

        return view('schedules.create', compact('laboratories', 'dosens'));
    }

    public function show(LabSchedule $schedule)
    {
        $schedule->load(['laboratory', 'user']);

        return view('schedules.show', compact('schedule'));
    }

    public function edit(LabSchedule $schedule)
    {
        $laboratories = Laboratory::active()->orderBy('name')->get();
        $dosens = User::where('role', 'pengguna')->orderBy('name')->get();

        return view('schedules.edit', compact('schedule', 'laboratories', 'dosens'));
    }
}
