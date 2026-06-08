<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'total_points' => 'sometimes|integer|min:1',
            'components' => 'sometimes|array|min:1',
            'components.*.component_type' => 'required|in:lab,quiz,exam,project',
            'components.*.weight' => 'required|numeric|min:0|max:100',
            'components.*.raw_max' => 'required|numeric|min:0',
        ];
    }
}
