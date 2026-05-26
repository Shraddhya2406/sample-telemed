<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->name === 'patient';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'medical_history' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
