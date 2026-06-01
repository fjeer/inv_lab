<?php

namespace App\Http\Controllers\Api;

use App\Models\DamageReport;
use Illuminate\Http\Request;

class DamageReportApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = DamageReport::with(['equipment.laboratory', 'equipmentItem', 'reporter', 'handler']);
        $this->applyTrashedFilter($query, $request);

        if ($user->isPengguna()) {
            $query->where('reported_by', $user->id);
        }

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->whereHas('equipment', function ($qEq) use ($search) {
                    $qEq->where('name', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%');
                })->orWhereHas('equipmentItem', function ($qIt) use ($search) {
                    $qIt->where('qr_code', 'like', '%' . $search . '%');
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
        $limit = max($limit, 1); $page = (int)($start / $limit) + 1;

        $reports = $query->orderByDesc('id')->paginate($limit, ['*'], 'page', $page);

        return $this->sendPaginated($reports, 'Data laporan kerusakan berhasil dimuat');
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'equipment_id' => 'required|exists:equipment,id',
            'equipment_item_id' => 'required|exists:equipment_items,id',
            'damage_type' => 'required|in:ringan,sedang,berat',
            'description' => 'required|string',
            'incident_date' => 'required|date',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        // Verify equipment_item belongs to equipment
        $equipmentItem = \App\Models\EquipmentItem::where('id', $request->equipment_item_id)
            ->where('equipment_id', $request->equipment_id)
            ->first();

        if (!$equipmentItem) {
            return $this->sendError('Validation Error', ['equipment_item_id' => ['Item tidak sesuai dengan alat yang dipilih.']], 422);
        }

        $data = $request->only(['equipment_id', 'equipment_item_id', 'damage_type', 'description', 'incident_date']);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('damage-reports', 'public');
        }

        $report = DamageReport::create(array_merge($data, [
            'reported_by' => $request->user()->id,
            'status' => 'reported',
        ]));

        // Update equipment item condition automatically
        $newCondition = $request->damage_type === 'berat' ? 'rusak_berat' : 'rusak_ringan';
        
        $previousCondition = $equipmentItem->condition;
        $equipmentItem->update(['condition' => $newCondition]);

        // Create condition history
        \App\Models\EquipmentCondition::create([
            'equipment_id' => $equipmentItem->equipment_id,
            'equipment_item_id' => $equipmentItem->id,
            'checked_by' => $request->user()->id,
            'condition' => $newCondition,
            'previous_condition' => $previousCondition,
            'check_date' => now()->toDateString(),
            'description' => 'Otomatis dari Laporan Kerusakan: ' . $request->description
        ]);

        return $this->sendSuccess($report, 'Laporan kerusakan berhasil dibuat', 201);
    }

    public function show($id)
    {
        $report = DamageReport::withTrashed()->with(['equipment.laboratory', 'equipmentItem', 'reporter', 'handler'])->find($id);
        return $report ? $this->sendSuccess($report, 'Detail ditemukan') : $this->sendError('Not found');
    }

    public function updateStatus(Request $request, $id)
    {
        if (! $request->user()?->hasRole('admin_lab', 'asisten_lab', 'admin', 'asisten')) {
            return $this->sendError('Anda tidak memiliki akses untuk menangani laporan kerusakan', [], 403);
        }

        $report = DamageReport::with(['equipment', 'equipmentItem'])->find($id);
        if (!$report) {
            return $this->sendError('Laporan kerusakan tidak ditemukan');
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'status' => 'required|in:reported,in_review,in_repair,repaired,unrepairable,closed',
            'item_condition' => 'nullable|in:baik,rusak_ringan,rusak_berat,hilang',
            'repair_cost' => 'nullable|numeric|min:0',
            'repair_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $validated = $validator->validated();

        $newCondition = $validated['item_condition'] ?? match ($validated['status']) {
            'repaired' => 'baik',
            'unrepairable' => 'rusak_berat',
            default => null,
        };

        $data = [
            'status' => $validated['status'],
            'handled_by' => $request->user()->id,
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

        \Illuminate\Support\Facades\DB::transaction(function () use ($report, $data, $newCondition, $request, $validated) {
            $report->update($data);

            if (! $newCondition || ! $report->equipmentItem) {
                return;
            }

            $previousCondition = $report->equipmentItem->condition;
            $report->equipmentItem->update(['condition' => $newCondition]);
            $report->equipment->syncConditionFromItems();

            if ($previousCondition === $newCondition) {
                return;
            }

            \App\Models\EquipmentCondition::create([
                'equipment_id' => $report->equipment_id,
                'equipment_item_id' => $report->equipment_item_id,
                'checked_by' => $request->user()->id,
                'condition' => $newCondition,
                'previous_condition' => $previousCondition,
                'check_date' => now()->toDateString(),
                'description' => 'Update dari penanganan laporan kerusakan #' . $report->id,
                'action_taken' => $validated['repair_notes'] ?? null,
            ]);
        });

        return $this->sendSuccess($report->fresh(['equipment', 'equipmentItem', 'handler']), 'Status laporan kerusakan dan kondisi barang berhasil diperbarui');
    }

    public function destroy(Request $request, $id)
    {
        if (! $request->user()?->hasRole('admin_lab', 'admin')) {
            return $this->sendError('Anda tidak memiliki akses untuk menghapus laporan kerusakan', [], 403);
        }

        $report = DamageReport::find($id);
        if (! $report) {
            return $this->sendError('Laporan kerusakan tidak ditemukan');
        }

        $report->delete();

        return $this->sendSuccess(null, 'Laporan kerusakan berhasil dihapus');
    }

    public function forceDestroy(Request $request, $id)
    {
        if (! $request->user()?->hasRole('admin_lab', 'admin')) {
            return $this->sendError('Anda tidak memiliki akses untuk menghapus permanen laporan kerusakan', [], 403);
        }

        $report = DamageReport::onlyTrashed()->find($id);
        if (! $report) {
            return $this->sendError('Laporan kerusakan terhapus tidak ditemukan');
        }

        $report->forceDelete();

        return $this->sendSuccess(null, 'Laporan kerusakan berhasil dihapus permanen');
    }
}
