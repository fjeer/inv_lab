<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipment_id' => ['required', 'exists:equipment,id'],
            'equipment_item_id' => ['nullable', 'exists:equipment_items,id'],
            'condition' => ['required', 'in:baik,rusak_ringan,rusak_berat,hilang'],
            'check_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'action_taken' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
