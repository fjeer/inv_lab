<?php

namespace App\Http\Requests\Api;

use App\Models\LabBorrowing;
use Illuminate\Foundation\Http\FormRequest;

class StoreBorrowingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LabBorrowing::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'laboratory_id' => ['required', 'exists:laboratories,id'],
            'purpose' => ['required', 'string'],
            'activity_type' => ['nullable', 'string', 'max:100'],
            'borrow_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function toDto(int $userId): \App\Support\DTOs\CreateBorrowingData
    {
        return \App\Support\DTOs\CreateBorrowingData::fromRequest($this->validated(), $userId);
    }
}
