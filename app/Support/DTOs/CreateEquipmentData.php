<?php

namespace App\Support\DTOs;

class CreateEquipmentData
{
    public function __construct(
        public readonly int $laboratoryId,
        public readonly int $categoryId,
        public readonly string $name,
        public readonly string $code,
        public readonly int $quantity,
        public readonly string $condition,
        public readonly string $status,
        public readonly ?string $brand = null,
        public readonly ?string $model = null,
        public readonly ?string $serialNumber = null,
        public readonly ?int $yearAcquired = null,
        public readonly ?float $price = null,
        public readonly ?string $photo = null,
        public readonly ?string $description = null,
        public readonly ?string $notes = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            laboratoryId: (int) $data['laboratory_id'],
            categoryId: (int) $data['category_id'],
            name: $data['name'],
            code: $data['code'],
            quantity: (int) $data['quantity'],
            condition: $data['condition'],
            status: $data['status'],
            brand: $data['brand'] ?? null,
            model: $data['model'] ?? null,
            serialNumber: $data['serial_number'] ?? null,
            yearAcquired: isset($data['year_acquired']) ? (int) $data['year_acquired'] : null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            photo: $data['photo'] ?? null,
            description: $data['description'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'laboratory_id' => $this->laboratoryId,
            'category_id' => $this->categoryId,
            'name' => $this->name,
            'code' => $this->code,
            'quantity' => $this->quantity,
            'condition' => $this->condition,
            'status' => $this->status,
            'brand' => $this->brand,
            'model' => $this->model,
            'serial_number' => $this->serialNumber,
            'year_acquired' => $this->yearAcquired,
            'price' => $this->price,
            'photo' => $this->photo,
            'description' => $this->description,
            'notes' => $this->notes,
        ];
    }
}
