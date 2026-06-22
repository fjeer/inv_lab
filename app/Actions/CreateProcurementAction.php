<?php

namespace App\Actions;

use App\Models\EquipmentItem;
use App\Models\Procurement;
use App\Support\DTOs\CreateProcurementData;

final class CreateProcurementAction
{
    public function handle(CreateProcurementData $data): Procurement
    {
        $totalCost = array_reduce($data->items, function ($carry, $item) {
            return $carry + ($item['quantity'] * $item['estimated_price']);
        }, 0);

        $procurement = Procurement::create([
            'requested_by' => $data->requestedBy,
            'procurement_number' => Procurement::generateNumber(),
            'title' => $data->title,
            'description' => $data->description,
            'priority' => $data->priority,
            'total_estimated_cost' => $totalCost,
            'status' => 'submitted',
        ]);

        foreach ($data->items as $item) {
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

        return $procurement->load('items');
    }
}
