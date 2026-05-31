<?php

namespace App\Http\Controllers\Api;

use App\Models\EquipmentCondition;
use Illuminate\Http\Request;

class ConditionApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = EquipmentCondition::with(['equipment.laboratory', 'equipmentItem', 'checker']);

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->whereHas('equipment', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

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

        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $page = ($start / $limit) + 1;

        $conditions = $query->orderByDesc('check_date')->paginate($limit, ['*'], 'page', $page);

        return $this->sendPaginated($conditions, 'Data kondisi berhasil dimuat');
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'equipment_id' => 'required|exists:equipment,id',
            'equipment_item_id' => 'nullable|exists:equipment_items,id',
            'condition' => 'required|in:baik,rusak_ringan,rusak_berat,hilang',
            'check_date' => 'required|date',
            'description' => 'nullable|string',
            'action_taken' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $equipment = \App\Models\Equipment::findOrFail($request->equipment_id);
        
        $previousCondition = $equipment->condition;
        $equipmentItemId = $request->input('equipment_item_id');
        if ($equipmentItemId) {
            $item = \App\Models\EquipmentItem::where('id', $equipmentItemId)
                ->where('equipment_id', $equipment->id)
                ->first();
            if ($item) {
                $previousCondition = $item->condition;
                $item->update(['condition' => $request->condition]);
            }
        }

        $data = $request->only(['equipment_id', 'equipment_item_id', 'condition', 'check_date', 'description', 'action_taken']);
        
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('conditions', 'public');
        }

        $condition = EquipmentCondition::create(array_merge($data, [
            'checked_by' => $request->user()->id,
            'previous_condition' => $previousCondition,
        ]));

        $equipment->update(['condition' => $request->condition]);

        return $this->sendSuccess($condition, 'Pemeriksaan kondisi berhasil dicatat', 201);
    }

    public function destroy($id)
    {
        $condition = EquipmentCondition::find($id);
        if (!$condition) {
            return $this->sendError('Catatan tidak ditemukan');
        }

        // Delete photo if exists
        if ($condition->photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($condition->photo);
        }

        $condition->delete();

        return $this->sendSuccess(null, 'Catatan pemeriksaan kondisi berhasil dihapus');
    }
}
