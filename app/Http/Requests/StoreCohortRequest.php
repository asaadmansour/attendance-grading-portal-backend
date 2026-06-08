<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCohortRequest extends FormRequest
{
    public function rules(): array
    {
        $trackRule = ['required', 'exists:tracks,id'];

        // a track collects many cohorts over time, but only one can run at once
        if ($this->input('status', 'active') === 'active') {
            $trackRule[] = Rule::unique('cohorts', 'track_id')->where('status', 'active');
        }

        return [
            'track_id' => $trackRule,
            'name' => 'required|string|max:255',
            'status' => 'sometimes|in:planned,active,completed',
            'ta_ids' => 'sometimes|array',
            'ta_ids.*' => 'exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'track_id.unique' => 'This track already has an active cohort.',
        ];
    }
}
