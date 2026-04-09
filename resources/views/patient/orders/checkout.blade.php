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

            <!-- Order / Payment Form -->
            <form action="{{ route('patient.orders.place') }}" method="POST" class="mt-6 bg-gray-50 p-4 rounded" id="checkout-form">
                @csrf

                {{-- Global form errors --}}
                @if($errors->any())
                    <div class="mb-4">
                        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                            <ul class="list-disc pl-5">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="mt-1 block w-full rounded border-gray-300">
                            <option value="cod" {{ old('payment_method') === 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
                            <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                            <option value="upi" {{ old('payment_method') === 'upi' ? 'selected' : '' }}>UPI</option>
                        </select>
                        @error('payment_method') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-end justify-end">
                        <button type="submit" id="place-order-btn" class="bg-green-600 text-white px-4 py-2 rounded flex items-center gap-2">
                            <svg id="btn-spinner" class="w-4 h-4 animate-spin hidden" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                            <span id="btn-text">Place Order</span>
                        </button>
                    </div>
                </div>

                <!-- Card fields (shown only when payment_method == card) -->
                <div id="card-fields" class="mt-6 bg-white p-6 rounded-xl border border-gray-100 shadow-sm" style="display: {{ old('payment_method') === 'card' ? 'block' : 'none' }};">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Card Details</h3>
                        <div id="card-brand" class="text-sm text-gray-500 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 11h18M5 15h.01M9 15h6M15 15h.01"/></svg>
                            <span id="card-brand-text">Secure</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="card_number" class="block text-sm font-medium text-gray-700">Card Number</label>
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span id="card-icon" class="text-gray-400">💳</span>
                                </div>
                                <input id="card_number" name="card_number" value="{{ old('card_number') }}" inputmode="numeric" autocomplete="cc-number" maxlength="23" class="pl-10 pr-4 py-2 block w-full rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition" placeholder="1234 5678 9012 3456">
                            </div>
                            @error('card_number') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 mt-1">We use a secure, PCI-compliant provider. Your card details are never stored on our servers.</p>
                        </div>

                        <div class="grid grid-cols-3 gap-3 items-end">
                            <div>
                                <label for="expiry_month" class="block text-sm font-medium text-gray-700">Expiry</label>
                                <div class="flex gap-2">
                                    <select name="expiry_month" id="expiry_month" class="mt-1 block w-1/2 rounded-lg border border-gray-200 py-2 px-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" {{ old('expiry_month') == $m ? 'selected' : '' }}>{{ sprintf('%02d', $m) }}</option>
                                        @endfor
                                    </select>

                                    <select name="expiry_year" id="expiry_year" class="mt-1 block w-1/2 rounded-lg border border-gray-200 py-2 px-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        @php $currentYear = (int) date('Y'); @endphp
                                        @for($y = $currentYear; $y <= $currentYear + 10; $y++)
                                            <option value="{{ $y }}" {{ old('expiry_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                @error('expiry_month') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="cvv" class="block text-sm font-medium text-gray-700">CVV</label>
                                <input id="cvv" name="cvv" value="{{ old('cvv') }}" inputmode="numeric" autocomplete="cc-csc" maxlength="4" class="mt-1 block w-full rounded-lg border border-gray-200 py-2 px-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="123">
                                @error('cvv') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="text-right">
                                <span class="text-sm text-gray-600">Amount:</span>
                                <div class="font-semibold">₹{{ number_format($cart->total, 2) }}</div>
                            </div>
                        </div>

                        <hr class="my-4 border-t border-gray-100">

                        <h4 class="font-medium text-gray-700">Billing Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                            <div>
                                <label for="billing_first_name" class="block text-sm font-medium text-gray-700">First name</label>
                                <input id="billing_first_name" name="billing_first_name" value="{{ old('billing_first_name') ?? auth()->user()->name ?? '' }}" class="mt-1 block w-full rounded-lg border border-gray-200 py-2 px-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                @error('billing_first_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="billing_last_name" class="block text-sm font-medium text-gray-700">Last name</label>
                                <input id="billing_last_name" name="billing_last_name" value="{{ old('billing_last_name') }}" class="mt-1 block w-full rounded-lg border border-gray-200 py-2 px-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                @error('billing_last_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="billing_email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input id="billing_email" name="billing_email" value="{{ old('billing_email') ?? auth()->user()->email ?? '' }}" type="email" class="mt-1 block w-full rounded-lg border border-gray-200 py-2 px-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                @error('billing_email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="billing_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                <input id="billing_phone" name="billing_phone" value="{{ old('billing_phone') ?? auth()->user()->phone ?? '' }}" class="mt-1 block w-full rounded-lg border border-gray-200 py-2 px-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                @error('billing_phone') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="billing_address1" class="block text-sm font-medium text-gray-700">Address</label>
                                <input id="billing_address1" name="billing_address1" value="{{ old('billing_address1') }}" class="mt-1 block w-full rounded-lg border border-gray-200 py-2 px-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                @error('billing_address1') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="billing_city" class="block text-sm font-medium text-gray-700">City</label>
                                <input id="billing_city" name="billing_city" value="{{ old('billing_city') }}" class="mt-1 block w-full rounded-lg border border-gray-200 py-2 px-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>

                            <div>
                                <label for="billing_state" class="block text-sm font-medium text-gray-700">State</label>
                                <input id="billing_state" name="billing_state" value="{{ old('billing_state') }}" class="mt-1 block w-full rounded-lg border border-gray-200 py-2 px-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>

                            <div>
                                <label for="billing_zip" class="block text-sm font-medium text-gray-700">ZIP</label>
                                <input id="billing_zip" name="billing_zip" value="{{ old('billing_zip') }}" class="mt-1 block w-full rounded-lg border border-gray-200 py-2 px-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>

                            <div>
                                <label for="billing_country" class="block text-sm font-medium text-gray-700">Country</label>
                                <input id="billing_country" name="billing_country" value="{{ old('billing_country') ?? 'IN' }}" class="mt-1 block w-full rounded-lg border border-gray-200 py-2 px-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const pmSelect = document.getElementById('payment_method');
        const cardFields = document.getElementById('card-fields');
        const form = document.getElementById('checkout-form');
        const btn = document.getElementById('place-order-btn');
        const spinner = document.getElementById('btn-spinner');
        const btnText = document.getElementById('btn-text');
        const cardNumberInput = document.getElementById('card_number');
        const cardBrandText = document.getElementById('card-brand-text');
        const cvvInput = document.getElementById('cvv');

        function toggleCardFields() {
            if (!pmSelect) return;
            if (pmSelect.value === 'card') {
                cardFields.style.display = 'block';
                if (btnText) btnText.textContent = 'Pay Now';
            } else {
                cardFields.style.display = 'none';
                if (btnText) btnText.textContent = 'Place Order';
            }
        }

        if (pmSelect) pmSelect.addEventListener('change', toggleCardFields);

        // Format card number (groups of 4) and detect basic card brand
        function formatCardNumber(value) {
            const digits = value.replace(/\D/g, '').slice(0, 19);
            return digits.replace(/(.{4})/g, '$1 ').trim();
        }

        function detectCardBrand(value) {
            const d = value.replace(/\D/g, '');
            if (/^4/.test(d)) return 'Visa';
            if (/^5[1-5]/.test(d)) return 'Mastercard';
            if (/^3[47]/.test(d)) return 'American Express';
            if (/^6/.test(d)) return 'Discover';
            return null;
        }

        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function (ev) {
                const pos = cardNumberInput.selectionStart;
                const raw = cardNumberInput.value;
                const formatted = formatCardNumber(raw);
                cardNumberInput.value = formatted;

                const brand = detectCardBrand(formatted);
                if (brand) {
                    cardBrandText.textContent = brand;
                } else {
                    cardBrandText.textContent = 'Secure';
                }

                // Adjust CVV maxlength for Amex
                if (brand === 'American Express') {
                    cvvInput.setAttribute('maxlength', '4');
                } else {
                    cvvInput.setAttribute('maxlength', '3');
                }
            });
        }

        form.addEventListener('submit', function (ev) {
            // Show loading state
            btn.setAttribute('disabled', 'disabled');
            if (spinner) spinner.classList.remove('hidden');
            if (btnText) btnText.textContent = 'Processing...';
        });

        // initialize state
        toggleCardFields();
    });
</script>
@endsection
