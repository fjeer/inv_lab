<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\User;
use Illuminate\Http\Request;

class PatrolScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('patrol-schedules.index');
    }
}
