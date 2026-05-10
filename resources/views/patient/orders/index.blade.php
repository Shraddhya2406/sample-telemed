@extends('layouts.patient')

@section('title', 'Orders')
@section('page_title', 'Orders')
@section('eyebrow', 'Medicine history')

@section('content')
<div class="space-y-6 pb-20 lg:pb-0">
    <div class="flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-900">
        <div>
            <h2 class="text-2xl font-bold text-slate-950 dark:text-white">Order History</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Track medicine orders and review previous purchases.</p>
        </div>
        <a href="{{ route('patient.medicines.index') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700">Shop Medicines</a>
    </div>

    @if($orders->isEmpty())
        <div class="rounded-lg border border-slate-200 bg-white p-10 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-xl font-bold text-slate-950 dark:text-white">No orders yet</h3>
            <p class="mt-2 text-sm text-slate-500">Your medicine purchases will appear here after checkout.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
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
                <details class="group overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition open:shadow-lg dark:border-slate-800 dark:bg-slate-900">
                    <summary class="flex cursor-pointer list-none flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300">#{{ $order->id }}</span>
                            <div>
                                <p class="font-bold text-slate-950 dark:text-white">Order #{{ $order->id }}</p>
                                <p class="text-sm text-slate-500">Placed {{ $order->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-4 sm:justify-end">
                            <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $color }}">{{ ucfirst($status) }}</span>
                            <span class="text-lg font-bold text-slate-950 dark:text-white">Rs. {{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </summary>
                    <div class="border-t border-slate-100 px-5 py-5 dark:border-slate-800">
                        <div class="grid gap-4 md:grid-cols-[1fr_auto] md:items-start">
                            <div class="space-y-3">
                                @foreach($order->items->take(3) as $item)
                                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3 text-sm dark:bg-slate-950">
                                        <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $item->medicine->name ?? 'Deleted item' }} x {{ $item->quantity }}</span>
                                        <span class="text-slate-600 dark:text-slate-300">Rs. {{ number_format($item->quantity * $item->price, 2) }}</span>
                                    </div>
                                @endforeach
                                @if($order->items->count() > 3)
                                    <p class="text-sm text-slate-500">+{{ $order->items->count() - 3 }} more item{{ $order->items->count() - 3 === 1 ? '' : 's' }}</p>
                                @endif
                            </div>
                            <a href="{{ route('patient.orders.show', $order) }}" class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-5 py-3 text-sm font-bold text-blue-700 transition hover:bg-blue-100 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-300">View Details</a>
                        </div>
                    </div>
                </details>
            @endforeach
        </div>

        <div class="rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-slate-900">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
