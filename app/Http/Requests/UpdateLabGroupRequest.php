<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLabGroupRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'instructor_id' => 'sometimes|exists:users,id',
            'capacity' => 'sometimes|integer|min:1|max:50',
            'student_ids' => 'sometimes|array',
            'student_ids.*' => 'exists:users,id',
        ];
    }
}
