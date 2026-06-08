<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExcuseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_id' => ['required', 'integer', 'exists:attendances,id'],
            'reason' => ['required', 'string', 'max:2000'],
            'attachment' => [
                'nullable',
                'file',
                'max:1024',
                'mimes:pdf,jpg,jpeg,png,gif,webp',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'attachment.max' => 'The attachment may not be larger than 1 MB.',
            'attachment.mimes' => 'The attachment must be a PDF or an image (jpg, jpeg, png, gif, webp).',
        ];
    }
}