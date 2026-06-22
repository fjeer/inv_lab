<?php

namespace App\Actions;

use App\Models\ActivityLog;
use App\Models\LabBorrowing;

final class TransitionBorrowingAction
{
    public function complete(LabBorrowing $borrowing): LabBorrowing
    {
        $borrowing->update(['status' => 'completed']);

        $borrowing->load('laboratory');

        ActivityLog::log(
            'complete_borrowing',
            "Menyelesaikan peminjaman: {$borrowing->laboratory->name}",
            $borrowing,
        );

        return $borrowing;
    }

    public function cancel(LabBorrowing $borrowing): LabBorrowing
    {
        $borrowing->update(['status' => 'cancelled']);

        $borrowing->load('laboratory');

        ActivityLog::log(
            'cancel_borrowing',
            "Membatalkan peminjaman: {$borrowing->laboratory->name}",
            $borrowing,
        );

        return $borrowing;
    }
}
