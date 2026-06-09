<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLabGroupRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'instructor_id' => 'sometimes|exists:users,id',
            'capacity' => 'sometimes|integer|min:1|max:50',
            'student_ids' => 'sometimes|array',
            // a student can only join the group if they are enrolled in this cohort
            'student_ids.*' => [Rule::exists('enrollments', 'student_id')->where('cohort_id', $this->route('labGroup')->cohort_id)],
        ];
    }

    public function messages(): array
    {
        return [
            'student_ids.*.exists' => 'Each student must be enrolled in this cohort before joining a lab group.',
        ];
    }
}
