<?php

namespace App\Actions;

use App\Models\Equipment;
use App\Models\EquipmentCondition;
use App\Models\EquipmentItem;

final class RecordEquipmentConditionAction
{
    public function handle(array $data, int $userId): EquipmentCondition
    {
        $equipment = Equipment::findOrFail($data['equipment_id']);

        $previousCondition = $equipment->condition;
        $equipmentItemId = $data['equipment_item_id'] ?? null;

        if ($equipmentItemId) {
            $item = EquipmentItem::where('id', $equipmentItemId)
                ->where('equipment_id', $equipment->id)
                ->first();

            if ($item) {
                $previousCondition = $item->condition;
                $item->update(['condition' => $data['condition']]);
            }
        }

        $conditionData = [
            'equipment_id' => $data['equipment_id'],
            'equipment_item_id' => $equipmentItemId,
            'checked_by' => $userId,
            'condition' => $data['condition'],
            'previous_condition' => $previousCondition,
            'check_date' => $data['check_date'],
            'description' => $data['description'] ?? null,
            'action_taken' => $data['action_taken'] ?? null,
        ];

        if (isset($data['photo'])) {
            $conditionData['photo'] = $data['photo'];
        }

        $condition = EquipmentCondition::create($conditionData);

        if ($equipmentItemId) {
            $equipment->syncConditionFromItems();
        } else {
            $equipment->items()->update(['condition' => $data['condition']]);
            $equipment->syncConditionFromItems();
        }

        return $condition;
    }
}
