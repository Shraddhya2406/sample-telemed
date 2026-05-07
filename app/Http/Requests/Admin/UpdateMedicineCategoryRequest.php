<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateMedicineCategoryRequest extends StoreMedicineCategoryRequest
{
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('medicine_categories', 'name')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
