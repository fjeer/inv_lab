<?php

namespace App\Actions;

use App\Models\ActivityLog;
use App\Models\Equipment;
use App\Models\EquipmentItem;
use App\Models\Procurement;
use Illuminate\Support\Facades\DB;

final class ApproveProcurementAction
{
    public function handle(Procurement $procurement, int $approvedBy): Procurement
    {
        DB::transaction(function () use ($procurement, $approvedBy) {
            $procurement->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
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

        $procurement->load(['requester']);

        ActivityLog::log(
            'approve_procurement',
            "Menyetujui pengadaan: {$procurement->procurement_number} ({$procurement->title}) oleh {$procurement->requester?->name}",
            $procurement,
        );

        return $procurement->fresh();
    }
}
