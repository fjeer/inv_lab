<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateDamageReportAction;
use App\Actions\UpdateDamageReportStatusAction;
use App\Http\Requests\Api\StoreDamageReportRequest;
use App\Http\Requests\Api\UpdateDamageReportStatusRequest;
use App\Http\Resources\DamageReportResource;
use App\Models\DamageReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DamageReportApiController extends BaseApiController
{
    public function __construct(
        private readonly CreateDamageReportAction $createDamageReport,
        private readonly UpdateDamageReportStatusAction $updateDamageReportStatus,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = DamageReport::with(['equipment.laboratory', 'equipmentItem', 'reporter', 'handler']);
        $this->applyTrashedFilter($query, $request);

        if ($user->isPengguna()) {
            $query->where('reported_by', $user->id);
        }

        $search = $this->getSearch($request);

        if ($search !== null) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('equipment', fn ($qEq) => $qEq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                  ->orWhereHas('equipmentItem', fn ($qIt) => $qIt->where('qr_code', 'like', "%{$search}%"))
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('damage_type')) {
            $query->where('damage_type', $request->damage_type);
        }

        $perPage = $this->getPerPage($request);
        $page = $this->getPageFromRequest($request);

        $reports = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        return $this->sendPaginated($reports, 'Data laporan kerusakan berhasil dimuat');
    }

    public function store(StoreDamageReportRequest $request): JsonResponse
    {
        $report = $this->createDamageReport->handle($request->toDto($request->user()->id));

        return $this->sendSuccess(
            DamageReportResource::make($report),
            'Laporan kerusakan berhasil dibuat',
            201,
        );
    }

    public function show(DamageReport $damage_report): JsonResponse
    {
        $damage_report->load(['equipment.laboratory', 'equipmentItem', 'reporter', 'handler']);

        return $this->sendSuccess(
            DamageReportResource::make($damage_report),
            'Detail ditemukan',
        );
    }

    public function updateStatus(UpdateDamageReportStatusRequest $request, DamageReport $damage_report): JsonResponse
    {
        $report = $this->updateDamageReportStatus->handle(
            $damage_report,
            $request->validated(),
            $request->user()->id,
        );

        return $this->sendSuccess(
            DamageReportResource::make($report),
            'Status laporan kerusakan dan kondisi barang berhasil diperbarui',
        );
    }

    public function destroy(Request $request, DamageReport $damage_report): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return $this->sendError('Anda tidak memiliki akses untuk menghapus laporan kerusakan', [], 403);
        }

        $damage_report->delete();

        return $this->sendSuccess(null, 'Laporan kerusakan berhasil dihapus');
    }

    public function forceDestroy(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return $this->sendError('Anda tidak memiliki akses untuk menghapus permanen laporan kerusakan', [], 403);
        }

        $report = DamageReport::onlyTrashed()->findOrFail($id);
        $report->forceDelete();

        return $this->sendSuccess(null, 'Laporan kerusakan berhasil dihapus permanen');
    }
}
