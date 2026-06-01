<?php

namespace App\Http\Controllers\Api;

use App\Models\PatrolSchedule;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatrolScheduleApiController extends BaseApiController
{
    /**
     * List all patrol schedules with server-side pagination and filters.
     */
    public function index(Request $request)
    {
        $query = PatrolSchedule::with(['user', 'laboratory']);
        $this->applyTrashedFilter($query, $request);

        // DataTables search
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($qu) use ($search) {
                    $qu->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('laboratory', function ($ql) use ($search) {
                    $ql->where('name', 'like', '%' . $search . '%')
                       ->orWhere('code', 'like', '%' . $search . '%');
                })->orWhere('day_of_week', 'like', '%' . $search . '%');
            });
        }

        // Custom Filters
        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        // DataTables pagination: start (offset) and length (limit)
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $limit = max($limit, 1); $page = (int)($start / $limit) + 1;

        $schedules = $query->orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('start_time')
            ->paginate($limit, ['*'], 'page', $page);

        return $this->sendPaginated($schedules, 'Jadwal patroli berhasil dimuat');
    }

    /**
     * Show patrol schedule detail.
     */
    public function show($id)
    {
        $schedule = PatrolSchedule::withTrashed()->with(['user', 'laboratory'])->find($id);

        if (!$schedule) {
            return $this->sendError('Jadwal patroli tidak ditemukan');
        }

        return $this->sendSuccess($schedule, 'Detail jadwal patroli berhasil dimuat');
    }

    /**
     * Create new patrol schedule.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'laboratory_id' => 'required|exists:laboratories,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $schedule = PatrolSchedule::create($request->all());

        ActivityLog::log(
            'create_patrol_schedule',
            "Membuat jadwal patroli untuk asisten {$schedule->user?->name} di {$schedule->laboratory?->name}",
            $schedule
        );

        return $this->sendSuccess($schedule, 'Jadwal patroli berhasil ditambahkan', 201);
    }

    /**
     * Update patrol schedule.
     */
    public function update(Request $request, $id)
    {
        $schedule = PatrolSchedule::find($id);

        if (!$schedule) {
            return $this->sendError('Jadwal patroli tidak ditemukan');
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|exists:users,id',
            'laboratory_id' => 'sometimes|exists:laboratories,id',
            'day_of_week' => 'sometimes|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'status' => 'sometimes|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $schedule->update($request->all());

        ActivityLog::log(
            'update_patrol_schedule',
            "Memperbarui jadwal patroli untuk asisten {$schedule->user?->name} di {$schedule->laboratory?->name}",
            $schedule
        );

        return $this->sendSuccess($schedule, 'Jadwal patroli berhasil diperbarui');
    }

    /**
     * Delete patrol schedule.
     */
    public function destroy($id)
    {
        $schedule = PatrolSchedule::find($id);

        if (!$schedule) {
            return $this->sendError('Jadwal patroli tidak ditemukan');
        }

        $asisten = $schedule->user?->name;
        $lab = $schedule->laboratory?->name;
        $schedule->delete();

        ActivityLog::log(
            'delete_patrol_schedule',
            "Menghapus jadwal patroli asisten {$asisten} di {$lab}"
        );

        return $this->sendSuccess(null, 'Jadwal patroli berhasil dihapus');
    }
}
