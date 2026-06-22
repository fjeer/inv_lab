<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatrolScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin_lab', 'admin');
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'exists:users,id'],
            'laboratory_id' => ['sometimes', 'exists:laboratories,id'],
            'day_of_week' => ['sometimes', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i', 'after:start_time'],
            'status' => ['sometimes', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
