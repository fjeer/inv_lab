<?php

namespace App\Http\Controllers\Api;

use App\Actions\RecordEquipmentConditionAction;
use App\Http\Requests\Api\StoreConditionRequest;
use App\Http\Resources\ConditionResource;
use App\Models\EquipmentCondition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConditionApiController extends BaseApiController
{
    public function __construct(
        private readonly RecordEquipmentConditionAction $recordCondition,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = EquipmentCondition::with(['equipment.laboratory', 'equipmentItem', 'checker']);
        $this->applyTrashedFilter($query, $request);

        $search = $this->getSearch($request);

        if ($search !== null) {
            $query->whereHas('equipment', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        }

        if ($request->filled('laboratory_id')) {
            $query->whereHas('equipment', fn ($q) => $q->where('laboratory_id', $request->laboratory_id));
        }

        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        $perPage = $this->getPerPage($request);
        $page = $this->getPageFromRequest($request);

        $conditions = $query->orderByDesc('check_date')->paginate($perPage, ['*'], 'page', $page);

        return $this->sendPaginated($conditions, 'Data kondisi berhasil dimuat');
    }

    public function store(StoreConditionRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('conditions', 'public');
        }

        $condition = $this->recordCondition->handle($data, $request->user()->id);

        return $this->sendSuccess(
            ConditionResource::make($condition),
            'Pemeriksaan kondisi berhasil dicatat',
            201,
        );
    }

    public function destroy(EquipmentCondition $condition): JsonResponse
    {
        if ($condition->photo) {
            Storage::disk('public')->delete($condition->photo);
        }

        $condition->delete();

        return $this->sendSuccess(null, 'Catatan pemeriksaan kondisi berhasil dihapus');
    }
}
