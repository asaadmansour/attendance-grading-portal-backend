<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEngagementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // only people who actually teach can be booked: instructors and TAs
            'instructor_id' => ['required', Rule::exists('users', 'id')->whereIn('role', ['instructor', 'track_admin'])],
            'engagement_type' => 'required|in:lecture,lab,business',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'scheduled_hours_per_session' => 'required|numeric|min:0',
        ];
    }
}
