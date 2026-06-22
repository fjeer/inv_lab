<?php

namespace App\Actions;

use App\Models\ActivityLog;
use App\Models\Equipment;
use App\Support\DTOs\CreateEquipmentData;

final class CreateEquipmentAction
{
    public function handle(CreateEquipmentData $data): Equipment
    {
        $equipment = Equipment::create($data->toArray());

        ActivityLog::log(
            'create_equipment',
            "Menambahkan alat baru: {$equipment->name} ({$equipment->code})",
            $equipment,
        );

        return $equipment;
    }
}
