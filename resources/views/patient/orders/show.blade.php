@extends('layouts.patient')

@section('title', 'Order #' . $order->id)
@section('page_title', 'Order Details')
@section('eyebrow', 'Medicine history')

@section('content')
@php
    $status = $order->status;
    $color = match($status) {
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:ring-amber-900',
        'confirmed' => 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-950 dark:text-blue-300 dark:ring-blue-900',
        'shipped' => 'bg-indigo-50 text-indigo-700 ring-indigo-200 dark:bg-indigo-950 dark:text-indigo-300 dark:ring-indigo-900',
        'delivered' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:ring-emerald-900',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-950 dark:text-rose-300 dark:ring-rose-900',
        default => 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700',
    };
@endphp

<div class="mx-auto max-w-5xl space-y-6 pb-20 lg:pb-0">
    <a href="{{ route('patient.orders.index') }}" class="inline-flex text-sm font-bold text-blue-700 dark:text-blue-300">Back to orders</a>

    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-3xl font-bold text-slate-950 dark:text-white">Order #{{ $order->id }}</h2>
                <p class="mt-2 text-sm text-slate-500">Placed {{ $order->created_at->format('M d, Y h:i A') }}</p>
                <p class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Payment: {{ strtoupper($order->payment_method ?? 'Not specified') }}</p>
            </div>
            <span class="w-fit rounded-full px-3 py-1 text-sm font-bold ring-1 {{ $color }}">{{ ucfirst($status) }}</span>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-[1fr_20rem]">
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                <h3 class="font-bold text-slate-950 dark:text-white">Items</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($order->items as $item)
                    <div class="grid gap-4 p-5 sm:grid-cols-[1fr_auto] sm:items-center">
                        <div>
                            <p class="font-bold text-slate-950 dark:text-white">{{ $item->medicine->name ?? 'Deleted item' }}</p>
                            <p class="mt-1 text-sm text-slate-500">Qty {{ $item->quantity }} x Rs. {{ number_format($item->price, 2) }}</p>
                        </div>
                        <p class="text-lg font-bold text-slate-950 dark:text-white">Rs. {{ number_format($item->quantity * $item->price, 2) }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <aside class="h-fit rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-bold text-slate-950 dark:text-white">Order Timeline</h3>
            <div class="mt-5 space-y-4">
                @foreach(['pending' => 'Order placed', 'confirmed' => 'Confirmed', 'shipped' => 'Shipped', 'delivered' => 'Delivered'] as $step => $label)
                    @php
                        $steps = ['pending' => 1, 'confirmed' => 2, 'shipped' => 3, 'delivered' => 4, 'cancelled' => 0];
                        $active = ($steps[$status] ?? 0) >= ($steps[$step] ?? 9);
                    @endphp
                    <div class="flex gap-3">
                        <span class="mt-1 h-3 w-3 rounded-full {{ $active ? 'bg-emerald-600' : 'bg-slate-300 dark:bg-slate-700' }}"></span>
                        <p class="text-sm font-semibold {{ $active ? 'text-slate-900 dark:text-white' : 'text-slate-500' }}">{{ $label }}</p>
                    </div>
                @endforeach
                @if($status === 'cancelled')
                    <div class="flex gap-3">
                        <span class="mt-1 h-3 w-3 rounded-full bg-rose-600"></span>
                        <p class="text-sm font-semibold text-rose-700 dark:text-rose-300">Cancelled</p>
                    </div>
                @endif
            </div>
            <div class="mt-6 border-t border-slate-100 pt-5 dark:border-slate-800">
                <div class="flex justify-between text-xl font-bold text-slate-950 dark:text-white">
                    <span>Total</span>
                    <span>Rs. {{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </aside>
    </section>
</div>
@endsection
