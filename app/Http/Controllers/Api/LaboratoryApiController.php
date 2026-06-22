<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreLaboratoryRequest;
use App\Http\Requests\Api\UpdateLaboratoryRequest;
use App\Http\Resources\LaboratoryResource;
use App\Models\Laboratory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaboratoryApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Laboratory::with(['room.building', 'responsiblePerson']);
        $this->applyTrashedFilter($query, $request);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $search = $this->getSearch($request);

        if ($search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('room', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhereHas('building', fn ($ssq) => $ssq->where('name', 'like', "%{$search}%"));
                  });
            });
        }

        $perPage = $this->getPerPage($request);
        $page = $this->getPageFromRequest($request);

        $labs = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        return $this->sendPaginated($labs, 'Data laboratorium berhasil dimuat');
    }

    public function show(Laboratory $laboratory): JsonResponse
    {
        $laboratory->load(['room.building', 'equipment.category', 'patrolSchedules.user', 'responsiblePerson']);

        return $this->sendSuccess(
            LaboratoryResource::make($laboratory),
            'Detail laboratorium berhasil dimuat',
        );
    }

    public function store(StoreLaboratoryRequest $request): JsonResponse
    {
        $lab = Laboratory::create($request->validated());

        return $this->sendSuccess(
            LaboratoryResource::make($lab),
            'Laboratorium berhasil dibuat',
            201,
        );
    }

    public function update(UpdateLaboratoryRequest $request, Laboratory $laboratory): JsonResponse
    {
        $laboratory->update($request->validated());

        return $this->sendSuccess(
            LaboratoryResource::make($laboratory),
            'Laboratorium berhasil diperbarui',
        );
    }

    public function destroy(Laboratory $laboratory): JsonResponse
    {
        if ($laboratory->equipment()->count() > 0) {
            return $this->sendError('Laboratorium tidak dapat dihapus karena masih memiliki alat');
        }

        $laboratory->delete();

        return $this->sendSuccess(null, 'Laboratorium berhasil dihapus');
    }
}
