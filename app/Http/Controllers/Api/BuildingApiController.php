<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreBuildingRequest;
use App\Http\Requests\Api\UpdateBuildingRequest;
use App\Http\Resources\BuildingResource;
use App\Models\Building;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuildingApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Building::query();
        $this->applyTrashedFilter($query, $request);

        $search = $this->getSearch($request);

        if ($search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $query->with(['rooms.laboratories' => fn ($q) => $q->select('id', 'room_id', 'name')]);

        $perPage = $this->getPerPage($request);
        $page = $this->getPageFromRequest($request);

        $buildings = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        return $this->sendPaginated($buildings, 'Data gedung berhasil dimuat');
    }

    public function show(Building $building): JsonResponse
    {
        $building->load('rooms');

        return $this->sendSuccess(
            BuildingResource::make($building),
            'Detail gedung berhasil dimuat',
        );
    }

    public function store(StoreBuildingRequest $request): JsonResponse
    {
        $building = Building::create($request->validated());

        return $this->sendSuccess(
            BuildingResource::make($building),
            'Gedung berhasil dibuat',
            201,
        );
    }

    public function update(UpdateBuildingRequest $request, Building $building): JsonResponse
    {
        $building->update($request->validated());

        return $this->sendSuccess(
            BuildingResource::make($building),
            'Gedung berhasil diperbarui',
        );
    }

    public function destroy(Building $building): JsonResponse
    {
        if ($building->rooms()->count() > 0) {
            return $this->sendError('Gedung tidak dapat dihapus karena masih memiliki ruangan');
        }

        $building->delete();

        return $this->sendSuccess(null, 'Gedung berhasil dihapus');
    }
}
