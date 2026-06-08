<?php

namespace App\Http\Requests;

use App\Models\Cohort;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCohortRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:planned,active,completed',
        ];
    }

    // don't let a cohort go active if its track already has one running
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('status') !== 'active') {
                return;
            }

            $cohort = $this->route('cohort');

            $clash = Cohort::where('track_id', $cohort->track_id)
                ->where('status', 'active')
                ->whereKeyNot($cohort->id)
                ->exists();

            if ($clash) {
                $validator->errors()->add('status', 'This track already has an active cohort.');
            }
        });
    }
}
