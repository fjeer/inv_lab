<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentItem;
use Illuminate\Http\RedirectResponse;

class EquipmentItemController extends Controller
{
    public function destroy(Equipment $equipment, EquipmentItem $item): RedirectResponse
    {
        abort_unless($item->equipment_id === $equipment->id, 404);

        $item->delete();
        $equipment->updateQuietly([
            'quantity' => $equipment->items()->count(),
        ]);

        return back()->with('success', 'Item fisik berhasil dihapus sementara.');
    }

    public function restore(Equipment $equipment, int $item): RedirectResponse
    {
        $equipmentItem = $equipment->items()->onlyTrashed()->findOrFail($item);
        $equipmentItem->restore();
        $equipment->updateQuietly([
            'quantity' => $equipment->items()->count(),
        ]);

        return back()->with('success', 'Item fisik berhasil direstore.');
    }

    public function forceDestroy(Equipment $equipment, int $item): RedirectResponse
    {
        $equipmentItem = $equipment->items()->onlyTrashed()->findOrFail($item);

        if ($equipmentItem->damageReports()->exists()) {
            return back()->with('error', 'Item fisik tidak bisa dihapus permanen karena masih memiliki catatan laporan kerusakan.');
        }

        if ($equipmentItem->replacedByItems()->withTrashed()->exists()) {
            return back()->with('error', 'Item fisik tidak bisa dihapus permanen karena masih tercatat sebagai barang yang digantikan.');
        }

        $equipmentItem->forceDelete();

        return back()->with('success', 'Item fisik berhasil dihapus permanen.');
    }
}
