<?php

namespace App\Actions;

use App\Models\ActivityLog;
use App\Models\LabBorrowing;

final class RejectBorrowingAction
{
    public function handle(LabBorrowing $borrowing, int $rejectedBy, string $reason): LabBorrowing
    {
        $borrowing->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy,
            'rejection_reason' => $reason,
        ]);

        $borrowing->load(['laboratory', 'user']);

        ActivityLog::log(
            'reject_borrowing',
            "Menolak peminjaman: {$borrowing->laboratory->name} ({$borrowing->user->name})",
            $borrowing,
        );

        return $borrowing;
    }
}
