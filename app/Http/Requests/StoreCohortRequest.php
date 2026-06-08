<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCohortRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'track_id' => ['required', 'exists:tracks,id', Rule::unique('cohorts', 'track_id')->where('status', 'active')],
            'name' => 'required|string|max:255',
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
