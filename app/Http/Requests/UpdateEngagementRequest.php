<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEngagementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // only people who actually teach can be booked: instructors and TAs
            'instructor_id' => ['sometimes', Rule::exists('users', 'id')->whereIn('role', ['instructor', 'track_admin'])],
            'engagement_type' => 'sometimes|in:lecture,lab,business',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'scheduled_hours_per_session' => 'sometimes|numeric|min:0',
        ];
    }
}
