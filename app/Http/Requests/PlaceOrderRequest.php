<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Cart;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cart = $this->user()->cart;
            if (! $cart || $cart->items->isEmpty()) {
                $validator->errors()->add('cart', 'Your cart is empty.');
                return;
            }

            foreach ($cart->items as $item) {
                $medicine = $item->medicine;
                if (! $medicine || ! $medicine->is_active) {
                    $validator->errors()->add('cart', "Medicine '{$item->medicine_id}' is not available.");
                    return;
                }

                if ($item->quantity > $medicine->stock_quantity) {
                    $validator->errors()->add('cart', "Not enough stock for '{$medicine->name}'.");
                    return;
                }
            }
        });
    }
}
