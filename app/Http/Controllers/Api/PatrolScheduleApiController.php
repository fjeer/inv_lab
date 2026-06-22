<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreatePatrolScheduleAction;
use App\Http\Requests\Api\StorePatrolScheduleRequest;
use App\Http\Requests\Api\UpdatePatrolScheduleRequest;
use App\Http\Resources\PatrolScheduleResource;
use App\Models\ActivityLog;
use App\Models\PatrolSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatrolScheduleApiController extends BaseApiController
{
    public function __construct(
        private readonly CreatePatrolScheduleAction $createPatrolSchedule,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = PatrolSchedule::with(['user', 'laboratory']);
        $this->applyTrashedFilter($query, $request);

        $search = $this->getSearch($request);

        if ($search !== null) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($qu) => $qu->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('laboratory', fn ($ql) => $ql->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                  ->orWhere('day_of_week', 'like', "%{$search}%");
            });
        }

        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        $perPage = $this->getPerPage($request);
        $page = $this->getPageFromRequest($request);

        $schedules = $query
            ->orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('start_time')
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->sendPaginated($schedules, 'Jadwal patroli berhasil dimuat');
    }

    public function show(PatrolSchedule $patrol_schedule): JsonResponse
    {
        $patrol_schedule->load(['user', 'laboratory']);

        return $this->sendSuccess(
            PatrolScheduleResource::make($patrol_schedule),
            'Detail jadwal patroli berhasil dimuat',
        );
    }

    public function store(StorePatrolScheduleRequest $request): JsonResponse
    {
        $schedule = $this->createPatrolSchedule->handle($request->toDto());

        return $this->sendSuccess(
            PatrolScheduleResource::make($schedule),
            'Jadwal patroli berhasil ditambahkan',
            201,
        );
    }

    public function update(UpdatePatrolScheduleRequest $request, PatrolSchedule $patrol_schedule): JsonResponse
    {
        $patrol_schedule->update($request->validated());

        $patrol_schedule->load(['user', 'laboratory']);

        ActivityLog::log(
            'update_patrol_schedule',
            "Memperbarui jadwal patroli untuk asisten {$patrol_schedule->user?->name} di {$patrol_schedule->laboratory?->name}",
            $patrol_schedule,
        );

        return $this->sendSuccess(
            PatrolScheduleResource::make($patrol_schedule),
            'Jadwal patroli berhasil diperbarui',
        );
    }

    public function destroy(PatrolSchedule $patrol_schedule): JsonResponse
    {
        $asisten = $patrol_schedule->user?->name;
        $lab = $patrol_schedule->laboratory?->name;
        $patrol_schedule->delete();

        ActivityLog::log(
            'delete_patrol_schedule',
            "Menghapus jadwal patroli asisten {$asisten} di {$lab}",
        );

        return $this->sendSuccess(null, 'Jadwal patroli berhasil dihapus');
    }
}
