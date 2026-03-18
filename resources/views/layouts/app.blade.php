<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Sample Telemed'))</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center space-x-3">
                <x-logo size="36" :showText="false" class="block" />
                <span class="font-bold text-lg">{{ config('app.name', 'Sample Telemed') }}</span>
            </a>

            <nav class="flex items-center space-x-4">
                <a href="{{ url('/') }}" class="text-gray-700 hover:text-blue-600">Home</a>
                @auth
                    <a href="{{ url('/dashboard/patient') }}" class="text-gray-700 hover:text-blue-600">Dashboard</a>
                    <a href="{{ url('/patient/medicines') }}" class="text-gray-700 hover:text-blue-600">Store</a>

                    <!-- Cart link with persistent badge -->
                    <a href="{{ url('/patient/cart') }}" class="relative text-gray-700 hover:text-blue-600" id="cart-link">
                        Cart
                        @php
                            $cartCount = 0;
                            try {
                                if (Auth::check() && Auth::user()->cart) {
                                    $cartCount = Auth::user()->cart->items()->count();
                                }
                            } catch (\Throwable $e) {
                                $cartCount = 0;
                            }
                        @endphp
                        <span id="cart-count-badge" class="absolute -top-2 -right-6 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full" style="{{ $cartCount > 0 ? '' : 'display:none' }}">{{ $cartCount }}</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600">Login</a>
                    <a href="{{ route('register') }}" class="text-gray-700 hover:text-blue-600">Register</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="container mx-auto p-4">
        <!-- Flash messages -->
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-100 text-red-800 px-4 py-3 rounded">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Ajax flash placeholder -->
        <div id="ajax-flash" class="mb-4 hidden"></div>

        @yield('content')
    </main>

    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            function showAjaxFlash(message, success = true) {
                const el = document.getElementById('ajax-flash');
                el.classList.remove('hidden');
                el.innerHTML = message;
                el.className = success ? 'mb-4 bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded' : 'mb-4 bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded';
                setTimeout(() => { el.classList.add('hidden'); }, 4000);
            }

            function updateCartCount(count) {
                const badge = document.getElementById('cart-count-badge');
                if (!badge) return;
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.style.display = 'none';
                }
            }

            function formatCurrency(n) {
                return '₹' + Number(n).toFixed(2);
            }

            function handleAjaxResponse(res, onSuccess) {
                return res.json().then((data) => {
                    if (!res.ok) {
                        const msg = data.message || (data.errors ? Object.values(data.errors).flat().join('<br>') : 'Request failed');
                        showAjaxFlash(msg, false);
                        return null;
                    }
                    if (onSuccess) onSuccess(data);
                    return data;
                }).catch(() => {
                    showAjaxFlash('Invalid server response', false);
                    return null;
                });
            }

            /* ADD TO CART (existing) */
            function handleAjaxAdd(form) {
                const formData = new FormData(form);
                const action = form.getAttribute('action') || window.location.href;

                fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: formData,
                })
                .then((res) => handleAjaxResponse(res, (data) => {
                    const message = data.message || 'Item added to cart.';
                    const count = data.cart_count !== undefined ? data.cart_count : null;
                    showAjaxFlash(message, true);
                    if (count !== null) updateCartCount(count);
                }))
                .catch((err) => {
                    showAjaxFlash('Network error', false);
                    console.error(err);
                });
            }

            /* UPDATE CART ITEM */
            function handleAjaxUpdate(form) {
                const formData = new FormData(form);
                const action = form.getAttribute('action') || window.location.href;

                fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: formData,
                })
                .then((res) => handleAjaxResponse(res, (data) => {
                    // update item subtotal
                    if (data.item_id && typeof data.item_subtotal !== 'undefined') {
                        const subtotalEl = document.getElementById('item-subtotal-' + data.item_id);
                        if (subtotalEl) subtotalEl.textContent = formatCurrency(data.item_subtotal);
                    }
                    // update cart total
                    if (typeof data.cart_total !== 'undefined') {
                        const totalEl = document.getElementById('cart-total');
                        if (totalEl) totalEl.textContent = 'Total: ' + formatCurrency(data.cart_total);
                    }
                    // update badge
                    if (typeof data.cart_count !== 'undefined') updateCartCount(data.cart_count);

                    showAjaxFlash(data.message || 'Cart updated.', true);
                }))
                .catch((err) => {
                    showAjaxFlash('Network error', false);
                    console.error(err);
                });
            }

            /* REMOVE CART ITEM */
            function handleAjaxRemove(form) {
                const formData = new FormData(form);
                const action = form.getAttribute('action') || window.location.href;

                fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: formData,
                })
                .then((res) => handleAjaxResponse(res, (data) => {
                    const removedId = data.removed_item_id || form.dataset.itemId;
                    // remove row
                    const row = document.getElementById('cart-item-' + removedId);
                    if (row && row.parentNode) row.parentNode.removeChild(row);

                    // update cart total
                    if (typeof data.cart_total !== 'undefined') {
                        const totalEl = document.getElementById('cart-total');
                        if (totalEl) totalEl.textContent = 'Total: ' + formatCurrency(data.cart_total);
                    }

                    // update badge
                    if (typeof data.cart_count !== 'undefined') updateCartCount(data.cart_count);

                    // if cart empty, show empty message
                    if (data.cart_count === 0) {
                        const wrapper = document.getElementById('cart-wrapper');
                        if (wrapper) {
                            wrapper.innerHTML = '<div class="bg-white p-6 rounded shadow-sm">Your cart is empty. <a href="' + window.location.origin + '/patient/medicines' + '" class="text-blue-600">Browse medicines</a></div>';
                        }
                    }

                    showAjaxFlash(data.message || 'Item removed.', true);
                }))
                .catch((err) => {
                    showAjaxFlash('Network error', false);
                    console.error(err);
                });
            }

            /* CLEAR CART */
            function handleAjaxClear(form) {
                const formData = new FormData(form);
                const action = form.getAttribute('action') || window.location.href;

                fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: formData,
                })
                .then((res) => handleAjaxResponse(res, (data) => {
                    // replace cart wrapper with empty message
                    const wrapper = document.getElementById('cart-wrapper');
                    if (wrapper) {
                        wrapper.innerHTML = '<div class="bg-white p-6 rounded shadow-sm">Your cart is empty. <a href="' + window.location.origin + '/patient/medicines' + '" class="text-blue-600">Browse medicines</a></div>';
                    }

                    if (typeof data.cart_count !== 'undefined') updateCartCount(data.cart_count);
                    const totalEl = document.getElementById('cart-total');
                    if (totalEl) totalEl.textContent = 'Total: ' + formatCurrency(data.cart_total || 0);

                    showAjaxFlash(data.message || 'Cart cleared.', true);
                }))
                .catch((err) => {
                    showAjaxFlash('Network error', false);
                    console.error(err);
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                // add-to-cart forms
                document.querySelectorAll('form.ajax-add-to-cart').forEach(function (form) {
                    form.addEventListener('submit', function (ev) {
                        ev.preventDefault();
                        handleAjaxAdd(form);
                    });
                });

                // update quantity forms
                document.querySelectorAll('form.ajax-update-cart').forEach(function (form) {
                    form.addEventListener('submit', function (ev) {
                        ev.preventDefault();
                        handleAjaxUpdate(form);
                    });
                });

                // remove item forms
                document.querySelectorAll('form.ajax-remove-item').forEach(function (form) {
                    form.addEventListener('submit', function (ev) {
                        ev.preventDefault();
                        handleAjaxRemove(form);
                    });
                });

                // clear cart forms
                document.querySelectorAll('form.ajax-clear-cart').forEach(function (form) {
                    form.addEventListener('submit', function (ev) {
                        ev.preventDefault();
                        handleAjaxClear(form);
                    });
                });
            });
        })();
    </script>
</body>
</html>