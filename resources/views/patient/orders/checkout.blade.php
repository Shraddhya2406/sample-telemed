@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">Checkout</h1>

    @if(! $cart || $cart->items->isEmpty())
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
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart->items as $item)
                        <tr class="border-t">
                            <td class="py-3">{{ $item->medicine->name ?? 'Deleted item' }}</td>
                            <td class="py-3">₹{{ number_format($item->price, 2) }}</td>
                            <td class="py-3">{{ $item->quantity }}</td>
                            <td class="py-3">₹{{ number_format($item->sub_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4 text-right">
                <div class="text-lg font-semibold">Total: ₹{{ number_format($cart->total, 2) }}</div>
            </div>

            <form action="{{ route('patient.orders.place') }}" method="POST" class="mt-6 bg-gray-50 p-4 rounded">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <select name="payment_method" class="mt-1 block w-full rounded border-gray-300">
                            <!--
                            <option value="">Select (pay later)</option>
                            <option value="card">Card</option>
                            <option value="upi">UPI</option>
                            -->
                            <option value="cod">Cash on Delivery</option>
                        </select>
                    </div>

                    <div class="flex items-end justify-end">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Place Order</button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection
