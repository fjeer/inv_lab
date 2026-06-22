<?php

namespace App\Actions;

use App\Models\DamageReport;
use App\Models\EquipmentCondition;
use App\Models\EquipmentItem;
use App\Support\DTOs\CreateDamageReportData;

final class CreateDamageReportAction
{
    public function handle(CreateDamageReportData $data): DamageReport
    {
        $equipmentItem = EquipmentItem::where('id', $data->equipmentItemId)
            ->where('equipment_id', $data->equipmentId)
            ->firstOrFail();

        $reportData = [
            'equipment_id' => $data->equipmentId,
            'equipment_item_id' => $data->equipmentItemId,
            'damage_type' => $data->damageType,
            'description' => $data->description,
            'incident_date' => $data->incidentDate,
            'reported_by' => $data->reportedBy,
            'status' => 'reported',
        ];

        if ($data->photo) {
            $reportData['photo'] = $data->photo;
        }

        $report = DamageReport::create($reportData);

        $newCondition = $data->damageType === 'berat' ? 'rusak_berat' : 'rusak_ringan';
        $previousCondition = $equipmentItem->condition;

        $equipmentItem->update(['condition' => $newCondition]);

        EquipmentCondition::create([
            'equipment_id' => $equipmentItem->equipment_id,
            'equipment_item_id' => $equipmentItem->id,
            'checked_by' => $data->reportedBy,
            'condition' => $newCondition,
            'previous_condition' => $previousCondition,
            'check_date' => now()->toDateString(),
            'description' => 'Otomatis dari Laporan Kerusakan: ' . $data->description,
        ]);

        return $report;
    }
}
