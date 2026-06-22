<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePatrolScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin_lab', 'admin');
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'laboratory_id' => ['required', 'exists:laboratories,id'],
            'day_of_week' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function toDto(): \App\Support\DTOs\CreatePatrolScheduleData
    {
        return \App\Support\DTOs\CreatePatrolScheduleData::fromRequest($this->validated());
    }
}
