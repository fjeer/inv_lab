<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateEquipmentAction;
use App\Actions\DeleteEquipmentAction;
use App\Actions\UpdateEquipmentAction;
use App\Http\Requests\Api\StoreEquipmentRequest;
use App\Http\Requests\Api\UpdateEquipmentRequest;
use App\Http\Resources\EquipmentResource;
use App\Models\Equipment;
use App\Models\EquipmentItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentApiController extends BaseApiController
{
    public function __construct(
        private readonly CreateEquipmentAction $createEquipment,
        private readonly UpdateEquipmentAction $updateEquipment,
        private readonly DeleteEquipmentAction $deleteEquipment,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Equipment::with(['laboratory', 'category'])
            ->withCount([
                'items',
                'items as items_baik_count' => fn ($q) => $q->where('condition', 'baik'),
            ]);

        $this->applyTrashedFilter($query, $request);

        $search = $this->getSearch($request);

        if ($search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        $perPage = $this->getPerPage($request);
        $page = $this->getPageFromRequest($request);

        $equipment = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        return $this->sendPaginated($equipment, 'Data alat berhasil dimuat');
    }

    public function show(Equipment $equipment): JsonResponse
    {
        $equipment->load(['laboratory', 'category', 'conditions.checker']);

        return $this->sendSuccess(
            EquipmentResource::make($equipment),
            'Detail alat berhasil dimuat',
        );
    }

    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        $equipment = $this->createEquipment->handle($request->toDto());

        return $this->sendSuccess(
            EquipmentResource::make($equipment),
            'Alat berhasil ditambahkan',
            201,
        );
    }

    public function update(UpdateEquipmentRequest $request, Equipment $equipment): JsonResponse
    {
        $equipment = $this->updateEquipment->handle($equipment, $request->validated());

        return $this->sendSuccess(
            EquipmentResource::make($equipment),
            'Data alat berhasil diperbarui',
        );
    }

    public function destroy(Equipment $equipment): JsonResponse
    {
        $this->deleteEquipment->handle($equipment);

        return $this->sendSuccess(null, 'Alat berhasil dihapus');
    }

    public function scanQr(Request $request): JsonResponse
    {
        $qrCode = $request->input('qr_code');

        if (! $qrCode) {
            return $this->sendError('QR Code tidak boleh kosong', [], 400);
        }

        $item = EquipmentItem::where('qr_code', $qrCode)
            ->with(['equipment.laboratory'])
            ->first();

        if (! $item) {
            return $this->sendError('Alat tidak ditemukan dari QR Code ini', [], 404);
        }

        return $this->sendSuccess($item, 'Alat ditemukan');
    }
}
