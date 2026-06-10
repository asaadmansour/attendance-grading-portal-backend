<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => ['required_without:file', 'nullable', 'url', 'max:2048'],
            'file' => ['required_without:url', 'nullable', 'file', 'mimes:pdf,doc,docx,zip,png,jpg,jpeg', 'max:10240'],
        ];
    }
}
