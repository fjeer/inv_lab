<?php

namespace App\Http\Controllers\Api;

use App\Actions\ApproveProcurementAction;
use App\Actions\CreateProcurementAction;
use App\Http\Requests\Api\StoreProcurementRequest;
use App\Http\Resources\ProcurementResource;
use App\Models\Procurement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProcurementApiController extends BaseApiController
{
    public function __construct(
        private readonly CreateProcurementAction $createProcurement,
        private readonly ApproveProcurementAction $approveProcurement,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Procurement::with(['requester', 'items.replacesEquipment', 'items.replacesEquipmentItem']);
        $this->applyTrashedFilter($query, $request);

        if ($request->user()->isPengguna()) {
            $query->where('requested_by', $request->user()->id);
        }

        $search = $this->getSearch($request);

        if ($search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('procurement_number', 'like', "%{$search}%")
                  ->orWhereHas('requester', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $perPage = $this->getPerPage($request);
        $page = $this->getPageFromRequest($request);

        $procurements = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        return $this->sendPaginated($procurements, 'Data pengadaan berhasil dimuat');
    }

    public function show(Request $request, Procurement $procurement): JsonResponse
    {
        $procurement->load(['requester', 'items.replacesEquipment', 'items.replacesEquipmentItem', 'approver']);

        if ($request->user()->isPengguna() && $procurement->requested_by !== $request->user()->id) {
            return $this->sendError('Unauthorized', [], 403);
        }

        return $this->sendSuccess(
            ProcurementResource::make($procurement),
            'Detail pengadaan berhasil dimuat',
        );
    }

    public function store(StoreProcurementRequest $request): JsonResponse
    {
        $procurement = $this->createProcurement->handle($request->toDto($request->user()->id));

        return $this->sendSuccess(
            ProcurementResource::make($procurement),
            'Pengajuan pengadaan berhasil dibuat',
            201,
        );
    }

    public function approve(Request $request, Procurement $procurement): JsonResponse
    {
        $procurement = $this->approveProcurement->handle($procurement, $request->user()->id);

        return $this->sendSuccess(
            ProcurementResource::make($procurement),
            'Pengadaan berhasil disetujui',
        );
    }

    public function reject(Request $request, Procurement $procurement): JsonResponse
    {
        $request->validate(['rejection_reason' => 'required|string']);

        $procurement->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        return $this->sendSuccess(
            ProcurementResource::make($procurement),
            'Pengadaan berhasil ditolak',
        );
    }

    public function destroy(Procurement $procurement): JsonResponse
    {
        $procurement->delete();

        return $this->sendSuccess(null, 'Pengadaan berhasil dihapus');
    }
}
