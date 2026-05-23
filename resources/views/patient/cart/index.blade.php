@extends('layouts.patient')

@section('title', 'Cart')
@section('page_title', 'Your Cart')
@section('eyebrow', 'Review medicines')

@section('content')
<div class="pb-20 lg:pb-0">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-950 dark:text-white">Medicine Cart</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Confirm quantities before checkout. Your total updates when quantities change.</p>
        </div>
        <a href="{{ route('patient.medicines.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">Continue Shopping</a>
    </div>

    <div id="cart-wrapper">
        @if(!$cart || $cart->items->isEmpty())
            <div class="rounded-lg border border-slate-200 bg-white p-10 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-xl font-bold text-slate-950 dark:text-white">Your cart is empty</h3>
                <p class="mt-2 text-sm text-slate-500">Add medicines from the store to begin checkout.</p>
                <a href="{{ route('patient.medicines.index') }}" class="mt-5 inline-flex rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700">Browse Medicines</a>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
                <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="hidden grid-cols-[1.4fr_.6fr_.8fr_.6fr] gap-4 border-b border-slate-100 px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500 md:grid dark:border-slate-800">
                        <span>Medicine</span>
                        <span>Price</span>
                        <span>Quantity</span>
                        <span>Subtotal</span>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($cart->items as $item)
                            <div id="cart-item-{{ $item->id }}" class="grid gap-4 p-5 md:grid-cols-[1.4fr_.6fr_.8fr_.6fr] md:items-center">
                                <div class="flex items-center gap-4">
                                    @if($item->medicine?->image_url)
                                        <img src="{{ $item->medicine->image_url }}" alt="{{ $item->medicine->name }}" class="h-16 w-16 rounded-lg object-cover">
                                    @else
                                        <div class="h-16 w-16 rounded-lg bg-slate-100 dark:bg-slate-800"></div>
                                    @endif
                                    <div>
                                        <p class="font-bold text-slate-950 dark:text-white">{{ $item->medicine->name ?? 'Deleted item' }}</p>
                                        <p class="text-sm text-slate-500">{{ $item->medicine->category_name ?? 'Medicine' }}</p>
                                    </div>
                                </div>
                                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Rs. {{ number_format($item->price, 2) }}</div>
                                <div>
                                    <form action="{{ route('patient.cart.update') }}" method="POST" class="ajax-update-cart inline-flex items-center overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-950" data-item-id="{{ $item->id }}">
                                        @csrf
                                        <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                        <button type="button" data-cart-quantity-step="-1" class="flex h-9 w-9 items-center justify-center text-lg font-bold text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Decrease quantity" @disabled($item->quantity <= 1)>-</button>
                                        <input type="text" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->medicine->stock_quantity ?? $item->quantity }}" class="h-9 w-12 border-x border-slate-200 bg-transparent px-1 text-center text-sm font-bold text-slate-950 outline-none [appearance:textfield] focus:bg-blue-50 dark:border-slate-700 dark:text-white dark:focus:bg-slate-900 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" readonly>
                                        <button type="button" data-cart-quantity-step="1" class="flex h-9 w-9 items-center justify-center text-lg font-bold text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Increase quantity" @disabled($item->quantity >= ($item->medicine->stock_quantity ?? $item->quantity))>+</button>
                                    </form>
                                </div>
                                <div class="flex items-center justify-between gap-3 md:justify-end">
                                    <span id="item-subtotal-{{ $item->id }}" class="font-bold text-slate-950 dark:text-white">Rs. {{ number_format($item->sub_total, 2) }}</span>
                                    <form action="{{ route('patient.cart.remove') }}" method="POST" class="ajax-remove-item" data-item-id="{{ $item->id }}">
                                        @csrf
                                        <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-700 transition hover:bg-rose-100 dark:bg-rose-950 dark:text-rose-300" aria-label="Remove item" title="Remove item">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-10 0 .7 12.2A2 2 0 0 0 10.7 21h2.6a2 2 0 0 0 2-1.8L16 7M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2m-4 4v6m4-6v6"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <aside class="h-fit rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-lg font-bold text-slate-950 dark:text-white">Order Summary</h3>
                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span>Items</span>
                            <span id="cart-items-total">{{ $cart->items->sum('quantity') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span>Delivery</span>
                            <span>Calculated at checkout</span>
                        </div>
                        <div class="border-t border-slate-100 pt-4 dark:border-slate-800">
                            <div id="cart-total" class="flex justify-between text-xl font-bold text-slate-950 dark:text-white">
                                <span>Total:</span>
                                <span>Rs. {{ number_format($cart->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('patient.checkout') }}" class="mt-6 inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700">Proceed to Checkout</a>
                    <form action="{{ route('patient.cart.clear') }}" method="POST" class="ajax-clear-cart mt-3">
                        @csrf
                        <button type="submit" class="w-full rounded-lg border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 transition hover:border-rose-200 hover:text-rose-700 dark:border-slate-700 dark:text-slate-300">Clear Cart</button>
                    </form>
                </aside>
            </div>
        @endif
    </div>
</div>
@endsection
