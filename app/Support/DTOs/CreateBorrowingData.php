<?php

namespace App\Support\DTOs;

class CreateBorrowingData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $laboratoryId,
        public readonly string $purpose,
        public readonly ?string $activityType,
        public readonly string $borrowDate,
        public readonly string $startTime,
        public readonly string $endTime,
        public readonly ?string $notes,
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            userId: $userId,
            laboratoryId: (int) $data['laboratory_id'],
            purpose: $data['purpose'],
            activityType: $data['activity_type'] ?? null,
            borrowDate: $data['borrow_date'],
            startTime: $data['start_time'],
            endTime: $data['end_time'],
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'laboratory_id' => $this->laboratoryId,
            'purpose' => $this->purpose,
            'activity_type' => $this->activityType,
            'borrow_date' => $this->borrowDate,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'notes' => $this->notes,
            'status' => 'pending',
        ];
    }
}
