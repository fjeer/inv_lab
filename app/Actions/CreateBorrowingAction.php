<?php

namespace App\Actions;

use App\Models\ActivityLog;
use App\Models\LabBorrowing;
use App\Support\DTOs\CreateBorrowingData;

final class CreateBorrowingAction
{
    public function handle(CreateBorrowingData $data): LabBorrowing
    {
        $borrowing = LabBorrowing::create($data->toArray());

        $borrowing->load('laboratory');

        ActivityLog::log(
            'create_borrowing',
            "Mengajukan peminjaman: {$borrowing->laboratory->name} untuk {$borrowing->purpose}",
            $borrowing,
        );

        return $borrowing;
    }
}
