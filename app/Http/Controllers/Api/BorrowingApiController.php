<?php

namespace App\Http\Controllers\Api;

use App\Actions\ApproveBorrowingAction;
use App\Actions\CreateBorrowingAction;
use App\Actions\RejectBorrowingAction;
use App\Actions\TransitionBorrowingAction;
use App\Http\Requests\Api\StoreBorrowingRequest;
use App\Http\Resources\BorrowingResource;
use App\Models\LabBorrowing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BorrowingApiController extends BaseApiController
{
    public function __construct(
        private readonly CreateBorrowingAction $createBorrowing,
        private readonly ApproveBorrowingAction $approveBorrowing,
        private readonly RejectBorrowingAction $rejectBorrowing,
        private readonly TransitionBorrowingAction $transitionBorrowing,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = LabBorrowing::with(['user', 'laboratory']);
        $this->applyTrashedFilter($query, $request);

        $search = $this->getSearch($request);

        if ($search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'like', "%{$search}%")
                  ->orWhere('activity_type', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('laboratory', fn ($lq) => $lq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }

        if ($request->user()->isPengguna()) {
            $query->where('user_id', $request->user()->id);
        }

        $perPage = $this->getPerPage($request);
        $page = $this->getPageFromRequest($request);

        $borrowings = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        return $this->sendPaginated($borrowings, 'Data peminjaman berhasil dimuat');
    }

    public function show(LabBorrowing $borrowing): JsonResponse
    {
        $borrowing->load(['user', 'laboratory', 'approver']);

        return $this->sendSuccess(
            BorrowingResource::make($borrowing),
            'Detail ditemukan',
        );
    }

    public function store(StoreBorrowingRequest $request): JsonResponse
    {
        $borrowing = $this->createBorrowing->handle($request->toDto($request->user()->id));

        return $this->sendSuccess(
            BorrowingResource::make($borrowing),
            'Permohonan peminjaman berhasil diajukan',
            201,
        );
    }

    public function approve(Request $request, LabBorrowing $borrowing): JsonResponse
    {
        $borrowing = $this->approveBorrowing->handle($borrowing, $request->user()->id);

        return $this->sendSuccess(
            BorrowingResource::make($borrowing),
            'Peminjaman disetujui',
        );
    }

    public function reject(Request $request, LabBorrowing $borrowing): JsonResponse
    {
        $request->validate(['rejection_reason' => 'required|string']);

        $borrowing = $this->rejectBorrowing->handle(
            $borrowing,
            $request->user()->id,
            $request->input('rejection_reason'),
        );

        return $this->sendSuccess(
            BorrowingResource::make($borrowing),
            'Peminjaman ditolak',
        );
    }

    public function complete(LabBorrowing $borrowing): JsonResponse
    {
        $borrowing = $this->transitionBorrowing->complete($borrowing);

        return $this->sendSuccess(
            BorrowingResource::make($borrowing),
            'Peminjaman selesai',
        );
    }

    public function cancel(LabBorrowing $borrowing): JsonResponse
    {
        $borrowing = $this->transitionBorrowing->cancel($borrowing);

        return $this->sendSuccess(
            BorrowingResource::make($borrowing),
            'Peminjaman dibatalkan',
        );
    }

    public function destroy(LabBorrowing $borrowing): JsonResponse
    {
        $borrowing->delete();

        return $this->sendSuccess(null, 'Peminjaman berhasil dihapus');
    }
}
