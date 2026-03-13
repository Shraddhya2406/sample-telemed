<!-- resources/views/patient/orders/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">My Orders</h1>

    @if($orders->isEmpty())
        <div class="bg-white p-6 rounded shadow-sm">You have no orders yet. <a href="{{ route('patient.medicines.index') }}" class="text-blue-600">Shop medicines</a></div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-white p-4 rounded shadow-sm flex justify-between items-center">
                    <div>
                        <div class="font-semibold">Order #{{ $order->id }}</div>
                        <div class="text-sm text-gray-600">Placed: {{ $order->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-semibold">₹{{ number_format($order->total_amount, 2) }}</div>
                        <div class="mt-1">
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
                        <a href="{{ route('patient.orders.show', $order) }}" class="block mt-2 text-sm text-blue-600">View details</a>
                    </div>
                </div>
            @endforeach

            <div>
                {{ $orders->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
