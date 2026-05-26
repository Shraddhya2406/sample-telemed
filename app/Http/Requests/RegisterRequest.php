<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'confirmed',
                'regex:/^\S*$/',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'role_id' => 'required|exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'The password must not contain spaces.',
        ];
    }
}
