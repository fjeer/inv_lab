<?php

namespace App\Support\DTOs;

class CreateDamageReportData
{
    public function __construct(
        public readonly int $equipmentId,
        public readonly int $equipmentItemId,
        public readonly string $damageType,
        public readonly string $description,
        public readonly string $incidentDate,
        public readonly int $reportedBy,
        public readonly ?string $photo = null,
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            equipmentId: (int) $data['equipment_id'],
            equipmentItemId: (int) $data['equipment_item_id'],
            damageType: $data['damage_type'],
            description: $data['description'],
            incidentDate: $data['incident_date'],
            reportedBy: $userId,
            photo: $data['photo'] ?? null,
        );
    }
}
