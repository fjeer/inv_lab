<?php

namespace App\Http\Controllers\Api;

use App\Models\LabBorrowing;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BorrowingApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = LabBorrowing::with(['user', 'laboratory']);
        $this->applyTrashedFilter($query, $request);

        // DataTables search
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'like', '%' . $search . '%')
                  ->orWhere('activity_type', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('laboratory', function ($lq) use ($search) {
                      $lq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('laboratory_id')) {
            $query->where('laboratory_id', $request->laboratory_id);
        }

        // Filtering by current user if they are 'pengguna'
        if ($request->user() && $request->user()->role === 'pengguna') {
            $query->where('user_id', $request->user()->id);
        }

        // DataTables pagination: start (offset) and length (limit)
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $limit = max($limit, 1); $page = (int)($start / $limit) + 1;

        $borrowings = $query->orderByDesc('id')->paginate($limit, ['*'], 'page', $page);

        return $this->sendPaginated($borrowings, 'Data peminjaman berhasil dimuat');
    }

    public function show($id)
    {
        $borrowing = LabBorrowing::withTrashed()->with(['user', 'laboratory', 'approver'])->find($id);
        return $borrowing ? $this->sendSuccess($borrowing, 'Detail ditemukan') : $this->sendError('Not found');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'laboratory_id' => 'required|exists:laboratories,id',
            'purpose' => 'required|string',
            'activity_type' => 'nullable|string|max:100',
            'borrow_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $borrowing = LabBorrowing::create([
            'user_id' => $request->user()->id,
            'laboratory_id' => $request->laboratory_id,
            'purpose' => $request->purpose,
            'activity_type' => $request->activity_type,
            'borrow_date' => $request->borrow_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'notes' => $request->notes,
            'status' => 'pending'
        ]);

        ActivityLog::log(
            'create_borrowing',
            "Mengajukan peminjaman: {$borrowing->laboratory->name} untuk {$borrowing->purpose}",
            $borrowing
        );

        return $this->sendSuccess($borrowing, 'Permohonan peminjaman berhasil diajukan', 201);
    }

    public function approve(Request $request, $id)
    {
        $borrowing = LabBorrowing::with(['laboratory', 'user'])->find($id);
        if (!$borrowing) {
            return $this->sendError('Not found');
        }

        $borrowing->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now()
        ]);

        ActivityLog::log(
            'approve_borrowing',
            "Menyetujui peminjaman: {$borrowing->laboratory->name} ({$borrowing->user->name})",
            $borrowing
        );

        return $this->sendSuccess($borrowing, 'Peminjaman disetujui');
    }

    public function reject(Request $request, $id)
    {
        $borrowing = LabBorrowing::with(['laboratory', 'user'])->find($id);
        if (!$borrowing) {
            return $this->sendError('Not found');
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $borrowing->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'rejection_reason' => $request->rejection_reason
        ]);

        ActivityLog::log(
            'reject_borrowing',
            "Menolak peminjaman: {$borrowing->laboratory->name} ({$borrowing->user->name})",
            $borrowing
        );

        return $this->sendSuccess($borrowing, 'Peminjaman ditolak');
    }

    public function complete($id)
    {
        $borrowing = LabBorrowing::with('laboratory')->find($id);
        if (!$borrowing) {
            return $this->sendError('Not found');
        }

        $borrowing->update(['status' => 'completed']);

        ActivityLog::log(
            'complete_borrowing',
            "Menyelesaikan peminjaman: {$borrowing->laboratory->name}",
            $borrowing
        );

        return $this->sendSuccess($borrowing, 'Peminjaman selesai');
    }

    public function cancel($id)
    {
        $borrowing = LabBorrowing::with('laboratory')->find($id);
        if (!$borrowing) {
            return $this->sendError('Not found');
        }

        $borrowing->update(['status' => 'cancelled']);

        ActivityLog::log(
            'cancel_borrowing',
            "Membatalkan peminjaman: {$borrowing->laboratory->name}",
            $borrowing
        );

        return $this->sendSuccess($borrowing, 'Peminjaman dibatalkan');
    }

    public function destroy($id)
    {
        $borrowing = LabBorrowing::with(['laboratory', 'user'])->find($id);
        if (!$borrowing) {
            return $this->sendError('Peminjaman tidak ditemukan');
        }

        ActivityLog::log(
            'delete_borrowing',
            "Menghapus peminjaman: {$borrowing->laboratory->name} (Peminjam: {$borrowing->user->name})",
            $borrowing
        );

        $borrowing->delete();

        return $this->sendSuccess(null, 'Peminjaman berhasil dihapus');
    }
}
