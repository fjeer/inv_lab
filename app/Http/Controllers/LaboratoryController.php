<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class LaboratoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Laboratory::query()->with(['room.building', 'responsiblePerson'])->withCount('equipment');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('location', 'like', "%{$request->search}%")
                  ->orWhereHas('room', function ($sq) use ($request) {
                      $sq->where('name', 'like', "%{$request->search}%")
                         ->orWhereHas('building', function ($ssq) use ($request) {
                             $ssq->where('name', 'like', "%{$request->search}%");
                         });
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $laboratories = $query->orderByDesc('id')->paginate(10)->withQueryString();
        $buildings = Building::with(['rooms.laboratories' => function ($q) {
            $q->select('id', 'room_id', 'name');
        }])->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('laboratories.index', compact('laboratories', 'buildings', 'users'));
    }

    public function show(Laboratory $laboratory)
    {
        $laboratory->load(['room.building', 'equipment', 'schedules.user', 'borrowings.user', 'responsiblePerson']);

        return view('laboratories.show', compact('laboratory'));
    }
}
