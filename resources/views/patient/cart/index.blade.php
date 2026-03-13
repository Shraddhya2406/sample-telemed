<!-- resources/views/patient/cart/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">Your Cart</h1>

    <div id="cart-wrapper">
    @if(!$cart || $cart->items->isEmpty())
        <div class="bg-white p-6 rounded shadow-sm">Your cart is empty. <a href="{{ route('patient.medicines.index') }}" class="text-blue-600">Browse medicines</a></div>
    @else
        <div class="bg-white p-4 rounded shadow-sm">
            <table class="w-full table-auto">
                <thead>
                    <tr class="text-left text-sm text-gray-600">
                        <th class="py-2">Medicine</th>
                        <th class="py-2">Price</th>
                        <th class="py-2">Qty</th>
                        <th class="py-2">Subtotal</th>
                        <th class="py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart->items as $item)
                        <tr class="border-t" id="cart-item-{{ $item->id }}">
                            <td class="py-3">{{ $item->medicine->name ?? 'Deleted item' }}</td>
                            <td class="py-3">₹{{ number_format($item->price, 2) }}</td>
                            <td class="py-3">
                                <form action="{{ route('patient.cart.update') }}" method="POST" class="flex items-center gap-2 ajax-update-cart" data-item-id="{{ $item->id }}">
                                    @csrf
                                    <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->medicine->stock_quantity ?? $item->quantity }}" class="w-20 border rounded px-2 py-1">
                                    <button type="submit" class="bg-gray-700 text-white px-2 py-1 rounded text-sm">Update</button>
                                </form>
                            </td>
                            <td class="py-3" id="item-subtotal-{{ $item->id }}">₹{{ number_format($item->sub_total, 2) }}</td>
                            <td class="py-3">
                                <form action="{{ route('patient.cart.remove') }}" method="POST" class="ajax-remove-item" data-item-id="{{ $item->id }}">
                                    @csrf
                                    <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                    <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4 flex justify-between items-center">
                <div>
                    <form action="{{ route('patient.cart.clear') }}" method="POST" class="ajax-clear-cart">
                        @csrf
                        <button type="submit" class="bg-yellow-600 text-white px-3 py-1 rounded">Clear cart</button>
                    </form>
                </div>
                <div class="text-right">
                    <div id="cart-total" class="text-lg font-semibold">Total: ₹{{ number_format($cart->total, 2) }}</div>
                    <a href="{{ route('patient.checkout') }}" class="inline-block mt-2 bg-green-600 text-white px-4 py-2 rounded">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    @endif
    </div>
</div>
@endsection
