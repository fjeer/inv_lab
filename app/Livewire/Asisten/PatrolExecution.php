<?php

namespace App\Livewire\Asisten;

use App\Models\EquipmentCondition;
use App\Models\EquipmentItem;
use App\Models\PatrolLog;
use App\Models\PatrolSchedule;
use Livewire\Component;

class PatrolExecution extends Component
{
    public ?PatrolSchedule $schedule = null;
    public $scheduleId;

    // Scan state
    public $scannedQrCode = '';
    public $scannedItem = null;
    public $scanError = '';
    public $showConditionForm = false;

    // Condition form
    public $selectedCondition = '';
    public $conditionNotes = '';

    // Log of items scanned in this session
    public $scannedLogs = [];
    public $totalItemsInLab = 0;

    public function mount(int $scheduleId): void
    {
        $this->scheduleId = $scheduleId;
        $this->schedule = PatrolSchedule::with(['laboratory.equipment.items', 'user'])
            ->findOrFail($scheduleId);

        $this->totalItemsInLab = $this->schedule->laboratory
            ->equipment
            ->sum(fn ($eq) => $eq->items->count());

        // Load logs already created today for this schedule
        $this->loadTodaysLogs();
    }

    public function loadTodaysLogs(): void
    {
        $this->scannedLogs = PatrolLog::where('patrol_schedule_id', $this->scheduleId)
            ->whereDate('checked_at', today())
            ->with('equipmentItem.equipment')
            ->orderByDesc('checked_at')
            ->get()
            ->toArray();
    }

    /**
     * Called from JavaScript when QR code is scanned.
     */
    public function onQrScanned(string $qrCode): void
    {
        $this->scanError = '';
        $this->scannedItem = null;
        $this->showConditionForm = false;
        $this->selectedCondition = '';
        $this->conditionNotes = '';

        // Find the equipment item by QR code
        $item = EquipmentItem::where('qr_code', $qrCode)
            ->with(['equipment.laboratory'])
            ->first();

        if (! $item) {
            $this->scanError = 'QR Code tidak ditemukan dalam sistem.';
            return;
        }

        // Validate that this item belongs to the scheduled lab
        if ($item->equipment->laboratory_id !== $this->schedule->laboratory_id) {
            $this->scanError = 'Alat ini bukan milik laboratorium yang dijadwalkan untuk patroli ini.';
            return;
        }

        // Check if already scanned today
        $alreadyScanned = PatrolLog::where('patrol_schedule_id', $this->scheduleId)
            ->where('equipment_item_id', $item->id)
            ->whereDate('checked_at', today())
            ->exists();

        if ($alreadyScanned) {
            $this->scanError = 'Alat ini sudah di-scan hari ini pada patroli ini.';
            return;
        }

        $this->scannedItem = $item->toArray();
        $this->scannedItem['equipment_name'] = $item->equipment->name;
        $this->scannedItem['lab_name'] = $item->equipment->laboratory->name;
        $this->selectedCondition = $item->condition;
        $this->showConditionForm = true;
    }

    /**
     * Submit condition update after scanning.
     */
    public function submitCondition(): void
    {
        $this->validate([
            'selectedCondition' => 'required|in:baik,rusak_ringan,rusak_berat,hilang',
        ]);

        $item = EquipmentItem::find($this->scannedItem['id']);

        if (! $item) {
            $this->scanError = 'Item tidak ditemukan.';
            return;
        }

        $previousCondition = $item->condition;

        // Create patrol log
        PatrolLog::create([
            'patrol_schedule_id' => $this->scheduleId,
            'equipment_item_id' => $item->id,
            'checked_by' => auth()->id(),
            'condition' => $this->selectedCondition,
            'previous_condition' => $previousCondition,
            'notes' => $this->conditionNotes ?: null,
            'checked_at' => now(),
        ]);

        // Update item condition
        $item->update([
            'condition' => $this->selectedCondition,
            'condition_notes' => $this->conditionNotes ?: $item->condition_notes,
            'last_checked_at' => now(),
            'last_checked_by' => auth()->id(),
        ]);

        // Record in EquipmentCondition module (Kondisi Barang)
        EquipmentCondition::create([
            'equipment_id' => $item->equipment_id,
            'equipment_item_id' => $item->id,
            'checked_by' => auth()->id(),
            'condition' => $this->selectedCondition,
            'previous_condition' => $previousCondition,
            'check_date' => now()->toDateString(),
            'description' => $this->conditionNotes ?: 'Pemeriksaan via patroli lab',
        ]);

        // Reset scan state
        $this->showConditionForm = false;
        $this->scannedItem = null;
        $this->scannedQrCode = '';
        $this->selectedCondition = '';
        $this->conditionNotes = '';

        $this->loadTodaysLogs();

        $this->dispatch('swal', title: 'Berhasil!', text: 'Kondisi alat berhasil diperbarui.', icon: 'success');
    }

    public function cancelScan(): void
    {
        $this->showConditionForm = false;
        $this->scannedItem = null;
        $this->scanError = '';
    }

    public function render()
    {
        return view('livewire.asisten.patrol-execution')
            ->layout('layouts.app');
    }
}
