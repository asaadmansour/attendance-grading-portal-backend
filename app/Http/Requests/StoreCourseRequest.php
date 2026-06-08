<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'components' => 'required|array|min:1',
            'components.*.component_type' => 'required|in:lab,quiz,exam,project',
            'components.*.weight' => 'required|numeric|min:0|max:100',
            'components.*.raw_max' => 'required|numeric|min:0',
        ];
    }

    // the components carve up the whole course, so their weights need to total 100
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
