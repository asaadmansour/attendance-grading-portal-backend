<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEngagementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'instructor_id' => 'sometimes|exists:users,id',
            'engagement_type' => 'sometimes|in:lecture,lab,business',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'scheduled_hours_per_session' => 'sometimes|numeric|min:0',
        ];
    }
}
