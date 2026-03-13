@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Order #{{ $order->id }}</h1>
                <p class="text-sm text-gray-600">Placed: {{ $order->created_at->format('M d, Y H:i') }}</p>
                <p class="mt-2 text-gray-700">Payment: {{ $order->payment_method ?? 'Not specified' }}</p>
            </div>

            <div class="text-right">
                @php
                    $status = $order->status;
                    $color = match($status) {
                        'pending' => 'bg-yellow-200 text-yellow-800',
                        'confirmed' => 'bg-blue-200 text-blue-800',
                        'shipped' => 'bg-indigo-200 text-indigo-800',
                        'delivered' => 'bg-green-200 text-green-800',
                        'cancelled' => 'bg-red-200 text-red-800',
                        default => 'bg-gray-200 text-gray-800',
                    };
                @endphp
                <span class="px-3 py-1 rounded text-sm {{ $color }}">{{ ucfirst($status) }}</span>
            </div>
        </div>

        <hr class="my-4">

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
                @foreach($order->items as $item)
                    <tr class="border-t">
                        <td class="py-3">{{ $item->medicine->name ?? 'Deleted' }}</td>
                        <td class="py-3">₹{{ number_format($item->price, 2) }}</td>
                        <td class="py-3">{{ $item->quantity }}</td>
                        <td class="py-3">₹{{ number_format($item->quantity * $item->price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4 flex justify-between items-center">
            <a href="{{ route('patient.orders.index') }}" class="text-sm text-blue-600">&larr; Back to orders</a>
            <div class="text-right">
                <div class="text-lg font-semibold">Total: ₹{{ number_format($order->total_amount, 2) }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
