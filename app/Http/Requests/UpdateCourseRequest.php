<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'components' => 'sometimes|array|min:1',
            'components.*.component_type' => 'required|in:lab,quiz,exam,project',
            'components.*.weight' => 'required|numeric|min:0|max:100',
            'components.*.raw_max' => 'required|numeric|min:0',
        ];
    }

    // a replacement set of components needs to total 100 just the same
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $components = $this->input('components', []);

            if ($components && array_sum(array_column($components, 'weight')) != 100) {
                $validator->errors()->add('components', 'Component weights must add up to 100.');
            }
        });
    }
}
