<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->whereIn('name', ['admin', 'patient'])),
            ],
        ];
    }
}
