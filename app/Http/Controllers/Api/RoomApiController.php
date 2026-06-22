<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreRoomRequest;
use App\Http\Requests\Api\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Room::with('building');
        $this->applyTrashedFilter($query, $request);

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }

        $search = $this->getSearch($request);

        if ($search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $perPage = $this->getPerPage($request);
        $page = $this->getPageFromRequest($request);

        $rooms = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        return $this->sendPaginated($rooms, 'Data ruangan berhasil dimuat');
    }

    public function show(Room $room): JsonResponse
    {
        $room->load(['building', 'laboratories']);

        return $this->sendSuccess(
            RoomResource::make($room),
            'Detail ruangan berhasil dimuat',
        );
    }

    public function store(StoreRoomRequest $request): JsonResponse
    {
        $room = Room::create($request->validated());

        return $this->sendSuccess(
            RoomResource::make($room->load('building')),
            'Ruangan berhasil dibuat',
            201,
        );
    }

    public function update(UpdateRoomRequest $request, Room $room): JsonResponse
    {
        $room->update($request->validated());

        return $this->sendSuccess(
            RoomResource::make($room->load('building')),
            'Ruangan berhasil diperbarui',
        );
    }

    public function destroy(Room $room): JsonResponse
    {
        if ($room->laboratories()->count() > 0) {
            return $this->sendError('Ruangan tidak dapat dihapus karena masih memiliki laboratorium');
        }

        $room->delete();

        return $this->sendSuccess(null, 'Ruangan berhasil dihapus');
    }
}
