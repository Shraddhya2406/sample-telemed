<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Medicine;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $medicineId = $this->input('medicine_id');
        if ($medicineId) {
            $medicine = Medicine::find($medicineId);
            if ($medicine) {
                $this->merge(['_medicine' => $medicine]);
            }
        }
    }

    public function rules(): array
    {
        $medicine = $this->input('_medicine');

        $max = $medicine ? $medicine->stock_quantity : null;

        $rules = [
            'medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];

        if ($max !== null) {
            $rules['quantity'][] = 'max:' . $max;
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $medicine = $this->input('_medicine');
            if ($medicine && ! $medicine->is_active) {
                $validator->errors()->add('medicine_id', 'Selected medicine is not available.');
            }
        });
    }
}
