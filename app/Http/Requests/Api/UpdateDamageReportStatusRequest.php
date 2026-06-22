<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDamageReportStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin_lab', 'asisten_lab', 'admin', 'asisten');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:reported,in_review,in_repair,repaired,unrepairable,closed'],
            'item_condition' => ['nullable', 'in:baik,rusak_ringan,rusak_berat,hilang'],
            'repair_cost' => ['nullable', 'numeric', 'min:0'],
            'repair_notes' => ['nullable', 'string'],
        ];
    }
}
