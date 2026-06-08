<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $callerRole = UserRole::from($this->user()->role);
        $targetRole = UserRole::tryFrom($this->input('role'));

        return $targetRole && in_array($targetRole, $callerRole->canCreate());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', new Enum(UserRole::class)],
            'expires_at' => 'nullable|date|after:now',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
