<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaboratoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:laboratories,code'],
            'room_id' => ['required', 'exists:rooms,id', 'unique:laboratories,room_id'],
            'location' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'responsible_person_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:active,inactive,maintenance'],
        ];
    }
}
