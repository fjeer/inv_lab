<?php

namespace App\Actions;

use App\Models\ActivityLog;
use App\Models\Equipment;

final class UpdateEquipmentAction
{
    public function handle(Equipment $equipment, array $data): Equipment
    {
        $equipment->update($data);

        ActivityLog::log(
            'update_equipment',
            "Memperbarui data alat: {$equipment->name}",
            $equipment,
        );

        return $equipment;
    }
}
