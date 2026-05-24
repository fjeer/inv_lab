<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\LabBorrowing;
use App\Models\Laboratory;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LabBorrowingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = LabBorrowing::with(['laboratory', 'user', 'approver']);

        // Pengguna only sees their own borrowings
        if ($user->isPengguna()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }

        $borrowings = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $laboratories = Laboratory::orderBy('name')->get();

        return view('borrowings.index', compact('borrowings', 'laboratories'));
    }

    public function create()
    {
        $laboratories = Laboratory::active()->orderBy('name')->get();

        return view('borrowings.create', compact('laboratories'));
    }

    public function show(LabBorrowing $borrowing)
    {
        $borrowing->load(['laboratory', 'user', 'approver']);

        return view('borrowings.show', compact('borrowing'));
    }

    /**
     * Jadwal peminjaman (calendar view).
     */
    public function schedule(Request $request)
    {
        $query = LabBorrowing::with(['laboratory', 'user'])
            ->whereIn('status', ['approved', 'ongoing']);

        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }

        $borrowings = $query->orderBy('borrow_date')->orderBy('start_time')->get();
        $laboratories = Laboratory::orderBy('name')->get();

        return view('borrowings.schedule', compact('borrowings', 'laboratories'));
    }
}
