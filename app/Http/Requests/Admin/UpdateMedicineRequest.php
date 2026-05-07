<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateMedicineRequest extends StoreMedicineRequest
{
    public function rules(): array
    {
        $medicineId = $this->route('medicine')?->id;

        return array_merge(parent::rules(), [
            'sku' => ['required', 'string', 'max:255', Rule::unique('medicines', 'sku')->ignore($medicineId)],
            'thumbnail_image_id' => ['nullable', 'integer', 'exists:medicine_images,id'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer', 'exists:medicine_images,id'],
        ]);
    }
}
