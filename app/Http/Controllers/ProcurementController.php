<?php

namespace App\Http\Controllers;

use App\Models\Procurement;
use App\Models\ProcurementItem;
use Illuminate\Http\Request;

class ProcurementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Procurement::with(['requester', 'approver'])->withCount('items');

        // Non-admin/asisten can only see their own requests
        if ($user->role === 'pengguna' || $user->role === 'asisten_lab') {
            $query->where('requested_by', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $procurements = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('procurements.index', compact('procurements'));
    }

    public function create()
    {
        return view('procurements.create');
    }

    public function show(Procurement $procurement)
    {
        $procurement->load(['requester', 'approver', 'items']);

        return view('procurements.show', compact('procurement'));
    }
}
