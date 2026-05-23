@extends('layouts.patient')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">Checkout</h1>

    @if(! $cart || $cart->items->isEmpty())
        <div class="bg-white p-6 rounded shadow-sm">
            Your cart is empty.
            <a href="{{ route('patient.medicines.index') }}" class="text-blue-600">Browse medicines</a>
        </div>
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
                            <td class="py-3">Rs. {{ number_format($item->price, 2) }}</td>
                            <td class="py-3">{{ $item->quantity }}</td>
                            <td class="py-3">Rs. {{ number_format($item->sub_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4 text-right">
                <div class="text-lg font-semibold">Total: Rs. {{ number_format($cart->total, 2) }}</div>
            </div>

            <form action="{{ route('patient.orders.place') }}" method="POST" class="mt-6 bg-gray-50 p-4 rounded" id="checkout-form">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="mt-1 block w-full rounded border-gray-300">
                            <option value="razorpay" {{ old('payment_method', 'razorpay') === 'razorpay' ? 'selected' : '' }}>Online Payment</option>
                            <option value="cod" {{ old('payment_method') === 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
                        </select>
                        @error('payment_method') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-end justify-end">
                        <button type="submit" id="place-order-btn" class="bg-green-600 text-white px-4 py-2 rounded flex items-center gap-2">
                            <svg id="btn-spinner" class="w-4 h-4 animate-spin hidden" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                            <span id="btn-text">Pay Now</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>

@if($cart && ! $cart->items->isEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const razorpayCheckoutUrl = 'https://checkout.razorpay.com/v1/checkout.js';
            const csrfToken = @json(csrf_token());
            const keyId = @json(config('services.razorpay.key_id'));
            const amount = @json((int) round($cart->total * 100));
            const currency = 'INR';
            const userName = @json(auth()->user()->name ?? '');
            const userEmail = @json(auth()->user()->email ?? '');
            const userContact = @json(auth()->user()->phone ?? '');
            const createOrderUrl = @json(route('razorpay.create-order'));
            const verifyPaymentUrl = @json(route('razorpay.verify-payment'));
            const orderShowBaseUrl = @json(url('/patient/orders'));

            const form = document.getElementById('checkout-form');
            const paymentMethod = document.getElementById('payment_method');
            const button = document.getElementById('place-order-btn');
            const spinner = document.getElementById('btn-spinner');
            const buttonText = document.getElementById('btn-text');
            function setLoading(isLoading, text) {
                button.disabled = isLoading;
                spinner.classList.toggle('hidden', !isLoading);
                buttonText.textContent = text || (paymentMethod.value === 'razorpay' ? 'Pay Now' : 'Place Order');
            }

            function showMessage(text, type) {
                window.showPatientToast?.(text, type === 'error' ? 'error' : 'success');
            }

            function loadRazorpayCheckout() {
                if (window.Razorpay) {
                    return Promise.resolve();
                }

                const existingScript = document.querySelector('script[data-razorpay-checkout]');
                if (existingScript) {
                    return new Promise((resolve, reject) => {
                        existingScript.addEventListener('load', () => resolve(), { once: true });
                        existingScript.addEventListener('error', () => reject(new Error('Unable to load Razorpay Checkout. Check your internet connection or browser blocker.')), { once: true });
                    });
                }

                return new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = razorpayCheckoutUrl;
                    script.async = true;
                    script.dataset.razorpayCheckout = 'true';
                    script.onload = () => {
                        if (window.Razorpay) {
                            resolve();
                            return;
                        }

                        reject(new Error('Razorpay Checkout loaded, but did not initialize. Please refresh and try again.'));
                    };
                    script.onerror = () => reject(new Error('Unable to load Razorpay Checkout. Check your internet connection or browser blocker.'));
                    document.head.appendChild(script);
                });
            }

            async function postJson(url, payload) {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data.message || 'Payment request failed.');
                }

                return data;
            }

            function updateButtonLabel() {
                buttonText.textContent = paymentMethod.value === 'razorpay' ? 'Pay Now' : 'Place Order';
            }

            paymentMethod.addEventListener('change', updateButtonLabel);

            form.addEventListener('submit', async function (event) {
                if (paymentMethod.value !== 'razorpay') {
                    setLoading(true, 'Placing order...');
                    return;
                }

                event.preventDefault();

                if (!keyId) {
                    showMessage('Payment gateway is not configured.', 'error');
                    return;
                }

                try {
                    setLoading(true, 'Loading checkout...');
                    await loadRazorpayCheckout();

                    setLoading(true, 'Creating order...');

                    const order = await postJson(createOrderUrl, {
                        amount: amount,
                        currency: currency,
                        receipt: 'cart_' + Date.now(),
                    });

                    const checkout = new Razorpay({
                        key: keyId,
                        amount: order.amount,
                        currency: order.currency,
                        name: @json(config('app.name')),
                        description: 'Medicine order payment',
                        order_id: order.order_id,
                        prefill: {
                            name: userName,
                            email: userEmail,
                            contact: userContact,
                        },
                        notes: {
                            source: 'sample-telemed-checkout',
                        },
                        theme: {
                            color: '#16a34a',
                        },
                        handler: async function (response) {
                            try {
                                setLoading(true, 'Verifying payment...');

                                const verification = await postJson(verifyPaymentUrl, {
                                    razorpay_payment_id: response.razorpay_payment_id,
                                    razorpay_order_id: response.razorpay_order_id,
                                    razorpay_signature: response.razorpay_signature,
                                });

                                showMessage('Payment verified. Redirecting to your order...', 'success');
                                window.location.href = orderShowBaseUrl + '/' + verification.order_id;
                            } catch (error) {
                                setLoading(false);
                                showMessage(error.message, 'error');
                            }
                        },
                        modal: {
                            ondismiss: function () {
                                setLoading(false);
                                showMessage('Payment was cancelled before completion.', 'error');
                            },
                        },
                    });

                    checkout.on('payment.failed', function (response) {
                        setLoading(false);
                        const error = response.error || {};
                        const reason = error.description || 'Payment failed. Please try again.';
                        const details = [error.code, error.reason, error.metadata && error.metadata.payment_id]
                            .filter(Boolean)
                            .join(' | ');

                        console.error('Razorpay payment failed', response);
                        showMessage(reason, 'error');

                        if (details) {
                            console.error('Razorpay failure details:', details);
                        }
                    });

                    setLoading(false);
                    checkout.open();
                } catch (error) {
                    setLoading(false);
                    showMessage(error.message, 'error');
                }
            });

            updateButtonLabel();
        });
    </script>
@endif
@endsection
