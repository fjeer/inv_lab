<?php

namespace App\Actions;

use App\Models\ActivityLog;
use App\Models\Equipment;

final class DeleteEquipmentAction
{
    public function handle(Equipment $equipment): void
    {
        $name = $equipment->name;
        $equipment->delete();

        ActivityLog::log(
            'delete_equipment',
            "Menghapus alat: {$name}",
        );
    }
}
