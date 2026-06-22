<?php

namespace App\Support\DTOs;

class CreateProcurementData
{
    public function __construct(
        public readonly int $requestedBy,
        public readonly string $title,
        public readonly string $priority,
        public readonly array $items,
        public readonly ?string $description = null,
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            requestedBy: $userId,
            title: $data['title'],
            priority: $data['priority'],
            items: $data['items'],
            description: $data['description'] ?? null,
        );
    }
}
