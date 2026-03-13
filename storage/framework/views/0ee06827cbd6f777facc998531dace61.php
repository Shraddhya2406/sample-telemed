<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', config('app.name', 'Sample Telemed')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <a href="<?php echo e(url('/')); ?>" class="flex items-center space-x-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span class="font-bold text-lg"><?php echo e(config('app.name', 'Sample Telemed')); ?></span>
            </a>

            <nav class="flex items-center space-x-4">
                <a href="<?php echo e(url('/')); ?>" class="text-gray-700 hover:text-blue-600">Home</a>
                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(url('/dashboard/patient')); ?>" class="text-gray-700 hover:text-blue-600">Dashboard</a>
                    <a href="<?php echo e(url('/patient/medicines')); ?>" class="text-gray-700 hover:text-blue-600">Store</a>

                    <!-- Cart link with persistent badge -->
                    <a href="<?php echo e(url('/patient/cart')); ?>" class="relative text-gray-700 hover:text-blue-600" id="cart-link">
                        Cart
                        <?php
                            $cartCount = 0;
                            try {
                                if (Auth::check() && Auth::user()->cart) {
                                    $cartCount = Auth::user()->cart->items()->count();
                                }
                            } catch (\Throwable $e) {
                                $cartCount = 0;
                            }
                        ?>
                        <span id="cart-count-badge" class="absolute -top-2 -right-6 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full" style="<?php echo e($cartCount > 0 ? '' : 'display:none'); ?>"><?php echo e($cartCount); ?></span>
                    </a>

                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-red-600 hover:text-red-800">Logout</button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="text-gray-700 hover:text-blue-600">Login</a>
                    <a href="<?php echo e(route('register')); ?>" class="text-gray-700 hover:text-blue-600">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container mx-auto p-4">
        <!-- Flash messages -->
        <?php if(session('success')): ?>
            <div class="mb-4 bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="mb-4 bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="mb-4 bg-red-50 border border-red-100 text-red-800 px-4 py-3 rounded">
                <ul class="list-disc pl-5">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($err); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Ajax flash placeholder -->
        <div id="ajax-flash" class="mb-4 hidden"></div>

        <?php echo $__env->yieldContent('content'); ?>
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
</html><?php /**PATH C:\wamp64\www\vibe_code\sample-telemed\resources\views/layouts/app.blade.php ENDPATH**/ ?>