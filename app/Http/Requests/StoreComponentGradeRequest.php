<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComponentGradeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_component_id' => [
                'required', 'exists:course_components,id',
                Rule::unique('component_grades')
                    ->where('student_id', $this->input('student_id'))
                    ->whereNull('deleted_at'),
            ],
            'student_id'=> 'required|exists:users,id',
            'raw_score' => 'required|numeric|min:0',
        ];
    }
}
