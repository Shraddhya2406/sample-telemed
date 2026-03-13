<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CartItem;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cartItemId = $this->input('cart_item_id');
        $cartItem = CartItem::find($cartItemId);
        if (! $cartItem) {
            return false;
        }

        return $this->user() && $cartItem->cart && $cartItem->cart->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'cart_item_id' => ['required', 'integer', 'exists:cart_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cartItem = CartItem::find($this->input('cart_item_id'));
            if (! $cartItem) {
                return;
            }

            $medicine = $cartItem->medicine;
            if (! $medicine || ! $medicine->is_active) {
                $validator->errors()->add('cart_item_id', 'Medicine is not available.');
                return;
            }

            $max = $medicine->stock_quantity;
            if ($this->input('quantity') > $max) {
                $validator->errors()->add('quantity', 'Requested quantity exceeds available stock.');
            }
        });
    }
}
