<?php

namespace App\Actions;

use App\Models\ActivityLog;
use App\Models\LabBorrowing;

final class ApproveBorrowingAction
{
    public function handle(LabBorrowing $borrowing, int $approvedBy): LabBorrowing
    {
        $borrowing->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        $borrowing->load(['laboratory', 'user']);

        ActivityLog::log(
            'approve_borrowing',
            "Menyetujui peminjaman: {$borrowing->laboratory->name} ({$borrowing->user->name})",
            $borrowing,
        );

        return $borrowing;
    }
}
