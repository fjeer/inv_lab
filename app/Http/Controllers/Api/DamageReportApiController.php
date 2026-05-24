<?php

namespace App\Http\Controllers\Api;

use App\Models\DamageReport;
use Illuminate\Http\Request;

class DamageReportApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = DamageReport::with(['equipment.laboratory', 'reporter', 'handler']);

        if ($user->isPengguna()) {
            $query->where('reported_by', $user->id);
        }

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->whereHas('equipment', function ($qEq) use ($search) {
                    $qEq->where('name', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%');
                })->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('damage_type')) {
            $query->where('damage_type', $request->damage_type);
        }

        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $page = ($start / $limit) + 1;

        $reports = $query->orderByDesc('id')->paginate($limit, ['*'], 'page', $page);

        return $this->sendPaginated($reports, 'Data laporan kerusakan berhasil dimuat');
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'equipment_id' => 'required|exists:equipment,id',
            'damage_type' => 'required|in:ringan,sedang,berat',
            'description' => 'required|string',
            'incident_date' => 'required|date',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $data = $request->only(['equipment_id', 'damage_type', 'description', 'incident_date']);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('damage-reports', 'public');
        }

        $report = DamageReport::create(array_merge($data, [
            'reported_by' => $request->user()->id,
            'status' => 'reported',
        ]));

        return $this->sendSuccess($report, 'Laporan kerusakan berhasil dibuat', 201);
    }

    public function show($id)
    {
        $report = DamageReport::with(['equipment.laboratory', 'reporter', 'handler'])->find($id);
        return $report ? $this->sendSuccess($report, 'Detail ditemukan') : $this->sendError('Not found');
    }

    public function updateStatus(Request $request, $id)
    {
        $report = DamageReport::with('equipment')->find($id);
        if (!$report) {
            return $this->sendError('Laporan kerusakan tidak ditemukan');
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'status' => 'required|in:reported,in_review,in_repair,repaired,unrepairable,closed',
            'repair_cost' => 'nullable|numeric|min:0',
            'repair_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $validated = $validator->validated();

        $data = [
            'status' => $validated['status'],
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ];

        if (isset($validated['repair_cost'])) {
            $data['repair_cost'] = $validated['repair_cost'];
        }

        if (isset($validated['repair_notes'])) {
            $data['repair_notes'] = $validated['repair_notes'];
        }

        if (in_array($validated['status'], ['repaired', 'unrepairable', 'closed'])) {
            $data['resolved_at'] = now();
        }

        $report->update($data);

        // Update equipment condition based on status
        if ($validated['status'] === 'repaired' && $report->equipment) {
            $report->equipment->update(['condition' => 'baik']);
        } elseif ($validated['status'] === 'unrepairable' && $report->equipment) {
            $report->equipment->update(['condition' => 'rusak_berat', 'status' => 'disposed']);
        }

        return $this->sendSuccess($report, 'Status laporan kerusakan berhasil diperbarui');
    }
}
