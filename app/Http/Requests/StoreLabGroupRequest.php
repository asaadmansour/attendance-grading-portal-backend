<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLabGroupRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'instructor_id' => 'required|exists:users,id',
            'capacity' => 'sometimes|integer|min:1|max:50',
            'student_ids' => 'sometimes|array',
            // a student can only join the group if they are enrolled in this cohort
            'student_ids.*' => [Rule::exists('enrollments', 'student_id')->where('cohort_id', $this->route('cohort')->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'student_ids.*.exists' => 'Each student must be enrolled in this cohort before joining a lab group.',
        ];
    }
}
