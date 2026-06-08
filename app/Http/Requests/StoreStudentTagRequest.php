<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentTagRequest extends FormRequest
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
            'tag_id' => [
                'required', 'exists:tags,id',
                Rule::unique('student_tags')
                    ->where('student_id', $this->route('student'))
                    ->where('course_id', $this->input('course_id'))
                    ->whereNull('deleted_at'),
            ],
            'course_id' => 'required|exists:courses,id',
        ];
    }
}
