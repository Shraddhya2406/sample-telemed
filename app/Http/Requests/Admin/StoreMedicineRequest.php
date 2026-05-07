<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class StoreMedicineRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'composition' => ['nullable', 'string'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'expiry_date' => ['required', 'date'],
            'sku' => ['required', 'string', 'max:255', Rule::unique('medicines', 'sku')],
            'category_id' => ['required', 'integer', 'exists:medicine_categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'images' => ['nullable', 'array', 'max:6'],
            'images.*' => ['image', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
