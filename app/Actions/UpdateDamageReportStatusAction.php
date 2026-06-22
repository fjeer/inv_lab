<?php

namespace App\Actions;

use App\Models\DamageReport;
use App\Models\EquipmentCondition;
use Illuminate\Support\Facades\DB;

final class UpdateDamageReportStatusAction
{
    public function handle(DamageReport $report, array $data, int $userId): DamageReport
    {
        $newCondition = $data['item_condition'] ?? match ($data['status']) {
            'repaired' => 'baik',
            'unrepairable' => 'rusak_berat',
            default => null,
        };

        $updateData = [
            'status' => $data['status'],
            'handled_by' => $userId,
        ];

        if (isset($data['repair_cost'])) {
            $updateData['repair_cost'] = $data['repair_cost'];
        }

        if (isset($data['repair_notes'])) {
            $updateData['repair_notes'] = $data['repair_notes'];
        }

        if (in_array($data['status'], ['repaired', 'unrepairable', 'closed'])) {
            $updateData['resolved_at'] = now();
        }

        DB::transaction(function () use ($report, $updateData, $newCondition, $userId, $data) {
            $report->update($updateData);

            if (! $newCondition || ! $report->equipmentItem) {
                return;
            }

            $previousCondition = $report->equipmentItem->condition;
            $report->equipmentItem->update(['condition' => $newCondition]);
            $report->equipment->syncConditionFromItems();

            if ($previousCondition === $newCondition) {
                return;
            }

            EquipmentCondition::create([
                'equipment_id' => $report->equipment_id,
                'equipment_item_id' => $report->equipment_item_id,
                'checked_by' => $userId,
                'condition' => $newCondition,
                'previous_condition' => $previousCondition,
                'check_date' => now()->toDateString(),
                'description' => 'Update dari penanganan laporan kerusakan #' . $report->id,
                'action_taken' => $data['repair_notes'] ?? null,
            ]);
        });

        return $report->fresh(['equipment', 'equipmentItem', 'handler']);
    }
}
