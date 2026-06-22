<?php

namespace App\Http\Requests\Api;

use App\Models\DamageReport;
use Illuminate\Foundation\Http\FormRequest;

class StoreDamageReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', DamageReport::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'equipment_id' => ['required', 'exists:equipment,id'],
            'equipment_item_id' => ['required', 'exists:equipment_items,id'],
            'damage_type' => ['required', 'in:ringan,sedang,berat'],
            'description' => ['required', 'string'],
            'incident_date' => ['required', 'date'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function toDto(int $userId): \App\Support\DTOs\CreateDamageReportData
    {
        $data = $this->validated();

        if ($this->hasFile('photo')) {
            $data['photo'] = $this->file('photo')->store('damage-reports', 'public');
        }

        return \App\Support\DTOs\CreateDamageReportData::fromRequest($data, $userId);
    }
}
