<?php

namespace App\Livewire\Admin;

use App\Models\Equipment;
use App\Models\EquipmentItem;
use App\Models\Laboratory;
use Livewire\Component;

class QrCodePrint extends Component
{
    public $laboratories;
    public $selectedLabId = '';
    public $equipmentList = [];
    public $selectedItems = [];
    public $selectAll = false;
    public $items = [];

    public function mount(): void
    {
        $this->laboratories = Laboratory::active()->orderBy('name')->get();
    }

    public function updatedSelectedLabId(): void
    {
        if ($this->selectedLabId) {
            $this->equipmentList = Equipment::where('laboratory_id', $this->selectedLabId)
                ->with('items')
                ->orderBy('name')
                ->get();
        } else {
            $this->equipmentList = [];
        }
        $this->selectedItems = [];
        $this->selectAll = false;
        $this->items = [];
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedItems = collect($this->equipmentList)
                ->flatMap(fn ($eq) => $eq->items->pluck('id'))
                ->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    public function loadSelectedItems(): void
    {
        if (empty($this->selectedItems)) {
            session()->flash('error', 'Pilih minimal satu item untuk dicetak.');
            return;
        }

        $this->items = EquipmentItem::whereIn('id', $this->selectedItems)
            ->with(['equipment.laboratory.room.building'])
            ->orderBy('qr_code')
            ->get();
    }

    public function selectAllFromEquipment(int $equipmentId): void
    {
        $equipment = Equipment::with('items')->find($equipmentId);
        if ($equipment) {
            $itemIds = $equipment->items->pluck('id')->toArray();
            $this->selectedItems = array_unique(array_merge($this->selectedItems, $itemIds));
        }
    }

    public function render()
    {
        return view('livewire.admin.qr-code-print')
            ->layout('layouts.app');
    }
}
