<?php

namespace App\Http\Controllers\Api;

use App\Models\Equipment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EquipmentApiController extends BaseApiController
{
    /**
     * List all equipment with pagination and filters.
     */
    public function index(Request $request)
    {
        $query = Equipment::with(['laboratory', 'category']);

        // DataTables search
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        // Standard Filters
        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        // DataTables pagination: start (offset) and length (limit)
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $page = ($start / $limit) + 1;

        $equipment = $query->orderBy('name')->paginate($limit, ['*'], 'page', $page);

        // Standard JSON for now, index.blade.php handler will map it
        return $this->sendPaginated($equipment, 'Data alat berhasil dimuat');
    }

    /**
     * Detail equipment.
     */
    public function show($id)
    {
        $equipment = Equipment::with(['laboratory', 'category', 'conditions.checker'])->find($id);

        if (!$equipment) {
            return $this->sendError('Alat tidak ditemukan');
        }

        return $this->sendSuccess($equipment, 'Detail alat berhasil dimuat');
    }

    /**
     * Create new equipment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'laboratory_id' => 'required|exists:laboratories,id',
            'category_id' => 'required|exists:equipment_categories,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:equipment,code',
            'quantity' => 'required|integer|min:0',
            'condition' => 'required|in:baik,rusak_ringan,rusak_berat,hilang',
            'status' => 'required|in:available,in_use,borrowed,maintenance,disposed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $equipment = Equipment::create($request->all());

        ActivityLog::log(
            'create_equipment',
            "Menambahkan alat baru: {$equipment->name} ({$equipment->code})",
            $equipment
        );

        return $this->sendSuccess($equipment, 'Alat berhasil ditambahkan', 201);
    }

    /**
     * Update equipment.
     */
    public function update(Request $request, $id)
    {
        $equipment = Equipment::find($id);

        if (!$equipment) {
            return $this->sendError('Alat tidak ditemukan');
        }

        $validator = Validator::make($request->all(), [
            'laboratory_id' => 'sometimes|exists:laboratories,id',
            'category_id' => 'sometimes|exists:equipment_categories,id',
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:equipment,code,' . $id,
            'quantity' => 'sometimes|integer|min:0',
            'condition' => 'sometimes|in:baik,rusak_ringan,rusak_berat,hilang',
            'status' => 'sometimes|in:available,in_use,borrowed,maintenance,disposed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $equipment->update($request->all());

        ActivityLog::log(
            'update_equipment',
            "Memperbarui data alat: {$equipment->name}",
            $equipment
        );

        return $this->sendSuccess($equipment, 'Data alat berhasil diperbarui');
    }

    /**
     * Delete equipment.
     */
    public function destroy($id)
    {
        $equipment = Equipment::find($id);

        if (!$equipment) {
            return $this->sendError('Alat tidak ditemukan');
        }

        $name = $equipment->name;
        $equipment->delete();

        ActivityLog::log(
            'delete_equipment',
            "Menghapus alat: {$name}"
        );

        return $this->sendSuccess(null, 'Alat berhasil dihapus');
    }
}
