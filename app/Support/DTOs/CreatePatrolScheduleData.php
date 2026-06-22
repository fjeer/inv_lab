<?php

namespace App\Support\DTOs;

class CreatePatrolScheduleData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $laboratoryId,
        public readonly string $dayOfWeek,
        public readonly string $startTime,
        public readonly string $endTime,
        public readonly string $status,
        public readonly ?string $notes = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            laboratoryId: (int) $data['laboratory_id'],
            dayOfWeek: $data['day_of_week'],
            startTime: $data['start_time'],
            endTime: $data['end_time'],
            status: $data['status'],
            notes: $data['notes'] ?? null,
        );
    }
}
