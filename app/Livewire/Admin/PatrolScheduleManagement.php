<?php

namespace App\Livewire\Admin;

use App\Models\Laboratory;
use App\Models\PatrolSchedule;
use App\Models\User;
use Livewire\Component;

class PatrolScheduleManagement extends Component
{
    public $schedules;
    public $assistants;
    public $laboratories;

    // Form state
    public $editingId = null;
    public $userId = '';
    public $laboratoryId = '';
    public $dayOfWeek = '';
    public $startTime = '';
    public $endTime = '';
    public $status = 'active';
    public $notes = '';

    public $showForm = false;

    protected $rules = [
        'userId' => 'required|exists:users,id',
        'laboratoryId' => 'required|exists:laboratories,id',
        'dayOfWeek' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        'startTime' => 'required|date_format:H:i',
        'endTime' => 'required|date_format:H:i|after:startTime',
    ];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->schedules = PatrolSchedule::with(['user', 'laboratory'])
            ->orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('start_time')
            ->get();

        // Load assistants (users with asisten role)
        $this->assistants = User::where(function ($q) {
            $q->where('role', 'asisten_lab')
              ->orWhereHas('roleRelation', fn ($r) => $r->where('name', 'asisten'));
        })->where('is_active', true)->orderBy('name')->get();

        $this->laboratories = Laboratory::active()->orderBy('name')->get();
    }

    public function openForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $schedule = PatrolSchedule::findOrFail($id);
        $this->editingId = $id;
        $this->userId = $schedule->user_id;
        $this->laboratoryId = $schedule->laboratory_id;
        $this->dayOfWeek = $schedule->day_of_week;
        $this->startTime = $schedule->start_time;
        $this->endTime = $schedule->end_time;
        $this->status = $schedule->status;
        $this->notes = $schedule->notes ?? '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'user_id' => $this->userId,
            'laboratory_id' => $this->laboratoryId,
            'day_of_week' => $this->dayOfWeek,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            PatrolSchedule::where('id', $this->editingId)->update($data);
            session()->flash('message', 'Jadwal patroli berhasil diperbarui.');
        } else {
            PatrolSchedule::create($data);
            session()->flash('message', 'Jadwal patroli berhasil dibuat.');
        }

        $this->showForm = false;
        $this->resetForm();
        $this->loadData();
    }

    public function delete(int $id): void
    {
        PatrolSchedule::destroy($id);
        $this->loadData();
        session()->flash('message', 'Jadwal patroli berhasil dihapus.');
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->userId = '';
        $this->laboratoryId = '';
        $this->dayOfWeek = '';
        $this->startTime = '';
        $this->endTime = '';
        $this->status = 'active';
        $this->notes = '';
    }

    public function render()
    {
        return view('livewire.admin.patrol-schedule-management')
            ->layout('layouts.app');
    }
}
