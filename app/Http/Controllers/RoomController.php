<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Building;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $buildings = Building::orderBy('name')->get();
        return view('rooms.index', compact('buildings'));
    }
}
