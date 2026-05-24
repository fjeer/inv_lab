<?php

namespace App\Http\Controllers\Api;

use App\Models\LabSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = LabSchedule::with(['laboratory', 'user']);

        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }

        if ($request->filled('day')) {
            $query->where('day_of_week', $request->day);
        }

        // DataTables search
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('class_group', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('laboratory', function ($lq) use ($search) {
                      $lq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->has('length')) {
            $limit = $request->input('length', 10);
            $start = $request->input('start', 0);
            $page = ($start / $limit) + 1;
            $schedules = $query->orderBy('day_of_week')->orderBy('start_time')->paginate($limit, ['*'], 'page', $page);
            return $this->sendPaginated($schedules, 'Jadwal berhasil dimuat');
        }

        $schedules = $query->orderBy('day_of_week')->orderBy('start_time')->get();

        return $this->sendSuccess($schedules, 'Jadwal berhasil dimuat');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'laboratory_id' => 'required|exists:laboratories,id',
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'semester' => 'nullable|string|max:20',
            'academic_year' => 'nullable|string|max:20',
            'class_group' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $schedule = LabSchedule::create($validator->validated());

        return $this->sendSuccess($schedule, 'Jadwal berhasil dibuat', 201);
    }

    public function update(Request $request, $id)
    {
        $schedule = LabSchedule::find($id);
        if (!$schedule) {
            return $this->sendError('Jadwal tidak ditemukan');
        }

        $validator = Validator::make($request->all(), [
            'laboratory_id' => 'required|exists:laboratories,id',
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'semester' => 'nullable|string|max:20',
            'academic_year' => 'nullable|string|max:20',
            'class_group' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $schedule->update($validator->validated());

        return $this->sendSuccess($schedule, 'Jadwal berhasil diperbarui');
    }

    public function destroy($id)
    {
        $schedule = LabSchedule::find($id);
        if (!$schedule) {
            return $this->sendError('Jadwal tidak ditemukan');
        }

        $schedule->delete();

        return $this->sendSuccess(null, 'Jadwal berhasil dihapus');
    }
}
