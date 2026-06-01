<?php

namespace App\Http\Controllers\Api;

use App\Models\Equipment;
use App\Models\EquipmentItem;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProcurementApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = Procurement::with(['requester', 'items.replacesEquipment', 'items.replacesEquipmentItem']);
        $this->applyTrashedFilter($query, $request);

        if ($request->user() && $request->user()->role !== 'admin_lab') {
            $query->where('requested_by', $request->user()->id);
        }

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('procurement_number', 'like', '%' . $search . '%')
                  ->orWhereHas('requester', function ($uq) use ($search) {
                      $uq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $limit = max($request->input('length', 10), 1);
        $start = $request->input('start', 0);
        $page = (int) ($start / $limit) + 1;

        $procurements = $query->orderByDesc('id')->paginate($limit, ['*'], 'page', $page);

        return $this->sendPaginated($procurements, 'Data pengadaan berhasil dimuat');
    }

    public function show($id)
    {
        $procurement = Procurement::withTrashed()
            ->with(['requester', 'items.replacesEquipment', 'items.replacesEquipmentItem', 'approver'])
            ->find($id);

        if (! $procurement) {
            return $this->sendError('Pengadaan tidak ditemukan');
        }

        if (request()->user()->role !== 'admin_lab' && $procurement->requested_by !== request()->user()->id) {
            return $this->sendError('Unauthorized', [], 403);
        }

        return $this->sendSuccess($procurement, 'Detail pengadaan berhasil dimuat');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.replaces_equipment_item_id' => 'nullable|exists:equipment_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $items = $request->input('items');
        $totalCost = array_reduce($items, function ($carry, $item) {
            return $carry + ($item['quantity'] * $item['estimated_price']);
        }, 0);

        $procurement = Procurement::create([
            'requested_by' => $request->user()->id,
            'procurement_number' => Procurement::generateNumber(),
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'total_estimated_cost' => $totalCost,
            'status' => 'submitted',
        ]);

        foreach ($items as $item) {
            $replacesEquipmentItemId = $item['replaces_equipment_item_id'] ?? null;
            $replacesEquipmentId = null;

            if ($replacesEquipmentItemId) {
                $replacesEquipmentId = EquipmentItem::find($replacesEquipmentItemId)?->equipment_id;
            }

            $procurement->items()->create([
                'item_name' => $item['item_name'],
                'replaces_equipment_id' => $replacesEquipmentId,
                'replaces_equipment_item_id' => $replacesEquipmentItemId,
                'specification' => $item['specification'] ?? null,
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'estimated_price' => $item['estimated_price'],
                'subtotal' => $item['quantity'] * $item['estimated_price'],
            ]);
        }

        return $this->sendSuccess($procurement->load('items'), 'Pengajuan pengadaan berhasil dibuat', 201);
    }

    public function approve(Request $request, $id)
    {
        $procurement = Procurement::with('items')->find($id);
        if (! $procurement) {
            return $this->sendError('Pengadaan tidak ditemukan');
        }

        DB::transaction(function () use ($procurement, $request) {
            $procurement->update([
                'status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);

            foreach ($procurement->items as $procurementItem) {
                if (! $procurementItem->replaces_equipment_item_id) {
                    continue;
                }

                $oldItem = EquipmentItem::find($procurementItem->replaces_equipment_item_id);
                if (! $oldItem) {
                    continue;
                }

                $oldEquipment = $oldItem->equipment;
                $oldItem->update(['condition' => 'rusak_berat']);

                $targetEquipment = Equipment::where('name', $procurementItem->item_name)->first();

                if (! $targetEquipment) {
                    $targetEquipment = Equipment::create([
                        'laboratory_id' => $oldEquipment?->laboratory_id,
                        'category_id' => $oldEquipment?->category_id,
                        'name' => $procurementItem->item_name,
                        'code' => Equipment::max('id') + 1 . '-' . str_replace(' ', '_', $procurementItem->item_name),
                        'quantity' => 0,
                        'condition' => 'baik',
                        'status' => 'available',
                    ]);
                }

                if ($procurementItem->replaces_equipment_id != $targetEquipment->id) {
                    $procurementItem->update(['replaces_equipment_id' => $targetEquipment->id]);
                }

                $lastSequence = (int) $targetEquipment->items()->withTrashed()->max('sequence_number');

                for ($i = 1; $i <= $procurementItem->quantity; $i++) {
                    $sequenceNumber = $lastSequence + $i;

                    $targetEquipment->items()->create([
                        'sequence_number' => $sequenceNumber,
                        'qr_code' => EquipmentItem::generateQrCode($targetEquipment, $sequenceNumber),
                        'condition' => 'baik',
                        'replaces_equipment_item_id' => $oldItem->id,
                    ]);
                }

                $targetEquipment->updateQuietly([
                    'quantity' => $targetEquipment->items()->count(),
                ]);
                $targetEquipment->syncConditionFromItems();

                if ($oldEquipment && $oldEquipment->isNot($targetEquipment)) {
                    $oldEquipment->updateQuietly([
                        'quantity' => $oldEquipment->items()->count(),
                    ]);
                    $oldEquipment->syncConditionFromItems();
                }
            }
        });

        return $this->sendSuccess($procurement, 'Pengadaan berhasil disetujui');
    }

    public function reject(Request $request, $id)
    {
        $procurement = Procurement::find($id);
        if (! $procurement) {
            return $this->sendError('Pengadaan tidak ditemukan');
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $procurement->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'rejection_reason' => $request->rejection_reason,
        ]);

        return $this->sendSuccess($procurement, 'Pengadaan berhasil ditolak');
    }

    public function destroy($id)
    {
        $procurement = Procurement::find($id);
        if (! $procurement) {
            return $this->sendError('Pengadaan tidak ditemukan');
        }

        $procurement->delete();

        return $this->sendSuccess(null, 'Pengadaan berhasil dihapus');
    }
}
