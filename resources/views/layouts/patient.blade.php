<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Patient Portal') - {{ config('app.name', 'Sample Telemed') }}</title>
    <script>
        if (localStorage.getItem('patient-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite('resources/css/app.css')
    <style>
        @media print {
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area { position: absolute; inset: 0; width: 100%; box-shadow: none !important; }
            #prescription-print-toolbar, .no-print { display: none !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
@php
    $user = Auth::user();
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard.patient', 'active' => request()->routeIs('dashboard.patient'), 'icon' => 'M3 13h8V3H3v10Zm10 8h8V3h-8v18ZM3 21h8v-6H3v6Z'],
        ['label' => 'Appointments', 'route' => 'patient.appointments.index', 'active' => request()->routeIs('patient.appointments.*'), 'icon' => 'M7 2v3M17 2v3M4 9h16M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z'],
        ['label' => 'Medicines', 'route' => 'patient.medicines.index', 'active' => request()->routeIs('patient.medicines.*'), 'icon' => 'M10 21a7 7 0 1 0 0-14 7 7 0 0 0 0 14ZM14.5 5.5l4 4M7 14l7-7'],
        ['label' => 'Orders', 'route' => 'patient.orders.index', 'active' => request()->routeIs('patient.orders.*') || request()->routeIs('patient.checkout'), 'icon' => 'M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4H6Zm3 8h6'],
        ['label' => 'Reports', 'route' => 'patient.prescriptions.index', 'active' => request()->routeIs('patient.prescriptions.*'), 'icon' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Zm0 0v6h6M8 13h8M8 17h5'],
        ['label' => 'Health Quiz', 'route' => 'patient.health-quiz', 'active' => request()->routeIs('patient.health-quiz*'), 'icon' => 'M12 21s-7-4.4-9-10a5.5 5.5 0 0 1 9-5.9A5.5 5.5 0 0 1 21 11c-2 5.6-9 10-9 10Z'],
        ['label' => 'Profile', 'route' => 'patient.profile', 'active' => request()->routeIs('patient.profile'), 'icon' => 'M20 21a8 8 0 1 0-16 0M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z'],
    ];
    $cartCount = 0;
    try {
        $cartCount = $user?->cart ? $user->cart->items()->count() : 0;
    } catch (\Throwable $e) {
        $cartCount = 0;
    }
@endphp

<div class="patient-shell">
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 border-r border-slate-200 bg-white/95 px-5 py-6 shadow-sm backdrop-blur xl:block dark:border-slate-800 dark:bg-slate-900/95">
        <a href="{{ route('dashboard.patient') }}" class="flex items-center gap-3">
            <x-logo size="40" :showText="false" class="block" />
            <div>
                <div class="text-lg font-bold tracking-tight text-slate-950 dark:text-white">{{ config('app.name', 'Sample Telemed') }}</div>
                <div class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Patient Care Portal</div>
            </div>
        </a>

        <nav class="mt-9 space-y-1">
            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}" class="group flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-semibold transition {{ $item['active'] ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div id="care-help-card" class="absolute bottom-6 left-5 right-5 rounded-lg border border-emerald-100 bg-emerald-50 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/40">
            <button type="button" id="care-help-minimize" class="absolute right-2 top-2 inline-flex h-7 w-7 items-center justify-center rounded-lg text-emerald-700 transition hover:bg-emerald-100 dark:text-emerald-300 dark:hover:bg-emerald-900/50" aria-label="Minimize care help">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>
            <p class="pr-7 text-sm font-semibold text-emerald-900 dark:text-emerald-100">Need help choosing care?</p>
            <p class="mt-1 text-xs leading-5 text-emerald-700 dark:text-emerald-300">Start with the quiz or book a doctor visit for personal guidance.</p>
            <a href="{{ route('patient.appointments.create') }}" class="mt-3 inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Book Doctor</a>
        </div>

        <button type="button" id="care-help-restore" class="absolute bottom-6 left-5 right-5 hidden rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-3 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200 dark:hover:bg-emerald-900/50">
            Need help choosing care?
        </button>
        </div>
    </aside>

    <div class="min-h-screen xl:pl-72">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
            <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <a href="{{ route('dashboard.patient') }}" class="flex items-center gap-2 xl:hidden">
                        <x-logo size="32" :showText="false" class="block" />
                    </a>
                    <div class="hidden min-w-0 sm:block">
                        <p class="truncate text-sm text-slate-500 dark:text-slate-400">@yield('eyebrow', 'Patient Portal')</p>
                        <h1 class="truncate text-lg font-bold text-slate-950 dark:text-white">@yield('page_title', 'Dashboard')</h1>
                    </div>
                </div>

                <nav class="hidden items-center gap-1 lg:flex xl:hidden">
                    @foreach(array_slice($navItems, 0, 5) as $item)
                        <a href="{{ route($item['route']) }}" class="rounded-lg px-3 py-2 text-sm font-semibold transition {{ $item['active'] ? 'bg-blue-50 text-blue-700 dark:bg-slate-800 dark:text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">{{ $item['label'] }}</a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-2">
                    <a href="{{ route('patient.cart.index') }}" id="cart-link" class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h15l-1.5 9h-12L6 6Zm0 0 0-2H3m6 17a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg>
                        <span id="cart-count-badge" class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1.5 text-xs font-bold text-white" style="{{ $cartCount > 0 ? '' : 'display:none' }}">{{ $cartCount }}</span>
                    </a>
                    <button type="button" id="theme-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" aria-label="Toggle dark mode">
                        <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.4-6.4-1.4 1.4M7 17l-1.4 1.4m12.8 0L17 17M7 7 5.6 5.6M12 8a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z"/></svg>
                        <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A8.5 8.5 0 1 1 11.2 3a6.5 6.5 0 0 0 9.8 9.8Z"/></svg>
                    </button>

                    <div class="relative">
                        <button type="button" id="profile-menu-button" class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white py-1.5 pl-2 pr-3 transition hover:border-blue-200 dark:border-slate-700 dark:bg-slate-900">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white">{{ strtoupper(substr($user?->name ?? 'P', 0, 1)) }}</span>
                            <span class="hidden max-w-32 truncate text-sm font-semibold sm:block">{{ $user?->name }}</span>
                        </button>
                        <div id="profile-menu" class="absolute right-0 mt-2 hidden w-56 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                            <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                                <p class="truncate text-sm font-semibold">{{ $user?->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $user?->email }}</p>
                            </div>
                            <a href="{{ route('patient.profile') }}" class="block px-4 py-3 text-sm text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">Profile overview</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-3 text-left text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            @if(session('success'))
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-200">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-200">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div id="ajax-flash" class="mb-5 hidden"></div>
            @yield('content')
        </main>

        <nav class="fixed bottom-0 left-0 right-0 z-30 grid grid-cols-5 border-t border-slate-200 bg-white/95 px-1 py-1 shadow-2xl backdrop-blur lg:hidden dark:border-slate-800 dark:bg-slate-900/95">
            @foreach(array_slice($navItems, 0, 5) as $item)
                <a href="{{ route($item['route']) }}" class="flex flex-col items-center gap-1 rounded-lg px-2 py-2 text-[11px] font-semibold {{ $item['active'] ? 'text-blue-700 dark:text-blue-300' : 'text-slate-500 dark:text-slate-400' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</div>

@include('call-popup')

<script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const profileButton = document.getElementById('profile-menu-button');
        const profileMenu = document.getElementById('profile-menu');
        const themeToggle = document.getElementById('theme-toggle');
        const careHelpCard = document.getElementById('care-help-card');
        const careHelpMinimize = document.getElementById('care-help-minimize');
        const careHelpRestore = document.getElementById('care-help-restore');

        profileButton?.addEventListener('click', () => profileMenu?.classList.toggle('hidden'));
        document.addEventListener('click', (event) => {
            if (!profileButton?.contains(event.target) && !profileMenu?.contains(event.target)) {
                profileMenu?.classList.add('hidden');
            }
        });

        themeToggle?.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('patient-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        });

        function setCareHelpMinimized(isMinimized) {
            careHelpCard?.classList.toggle('hidden', isMinimized);
            careHelpRestore?.classList.toggle('hidden', !isMinimized);
            localStorage.setItem('patient-care-help-minimized', isMinimized ? '1' : '0');
        }

        if (localStorage.getItem('patient-care-help-minimized') === '1') {
            setCareHelpMinimized(true);
        }

        careHelpMinimize?.addEventListener('click', () => setCareHelpMinimized(true));
        careHelpRestore?.addEventListener('click', () => setCareHelpMinimized(false));

        function showAjaxFlash(message, success = true) {
            const el = document.getElementById('ajax-flash');
            if (!el) return;
            el.classList.remove('hidden');
            el.innerHTML = message;
            el.className = success
                ? 'mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200'
                : 'mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-200';
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
            return 'Rs. ' + Number(n).toFixed(2);
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

        function postForm(form, callback) {
            fetch(form.getAttribute('action') || window.location.href, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: new FormData(form),
            })
            .then((res) => handleAjaxResponse(res, callback))
            .catch((err) => {
                showAjaxFlash('Network error', false);
                console.error(err);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form.ajax-add-to-cart').forEach((form) => {
                form.addEventListener('submit', (ev) => {
                    ev.preventDefault();
                    postForm(form, (data) => {
                        showAjaxFlash(data.message || 'Item added to cart.', true);
                        if (data.cart_count !== undefined) updateCartCount(data.cart_count);
                    });
                });
            });

            document.querySelectorAll('form.ajax-update-cart').forEach((form) => {
                form.addEventListener('submit', (ev) => {
                    ev.preventDefault();
                    postForm(form, (data) => {
                        if (data.item_id && typeof data.item_subtotal !== 'undefined') {
                            const subtotalEl = document.getElementById('item-subtotal-' + data.item_id);
                            if (subtotalEl) subtotalEl.textContent = formatCurrency(data.item_subtotal);
                        }
                        if (typeof data.cart_total !== 'undefined') {
                            const totalEl = document.getElementById('cart-total');
                            if (totalEl) totalEl.textContent = 'Total: ' + formatCurrency(data.cart_total);
                        }
                        if (typeof data.cart_count !== 'undefined') updateCartCount(data.cart_count);
                        showAjaxFlash(data.message || 'Cart updated.', true);
                    });
                });
            });

            document.querySelectorAll('form.ajax-remove-item').forEach((form) => {
                form.addEventListener('submit', (ev) => {
                    ev.preventDefault();
                    postForm(form, (data) => {
                        const removedId = data.removed_item_id || form.dataset.itemId;
                        document.getElementById('cart-item-' + removedId)?.remove();
                        if (typeof data.cart_total !== 'undefined') {
                            const totalEl = document.getElementById('cart-total');
                            if (totalEl) totalEl.textContent = 'Total: ' + formatCurrency(data.cart_total);
                        }
                        if (typeof data.cart_count !== 'undefined') updateCartCount(data.cart_count);
                        if (data.cart_count === 0) {
                            const wrapper = document.getElementById('cart-wrapper');
                            if (wrapper) wrapper.innerHTML = '<div class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">Your cart is empty. <a href="' + window.location.origin + '/patient/medicines' + '" class="font-semibold text-blue-600">Browse medicines</a></div>';
                        }
                        showAjaxFlash(data.message || 'Item removed.', true);
                    });
                });
            });

            document.querySelectorAll('form.ajax-clear-cart').forEach((form) => {
                form.addEventListener('submit', (ev) => {
                    ev.preventDefault();
                    postForm(form, (data) => {
                        const wrapper = document.getElementById('cart-wrapper');
                        if (wrapper) wrapper.innerHTML = '<div class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">Your cart is empty. <a href="' + window.location.origin + '/patient/medicines' + '" class="font-semibold text-blue-600">Browse medicines</a></div>';
                        if (typeof data.cart_count !== 'undefined') updateCartCount(data.cart_count);
                        const totalEl = document.getElementById('cart-total');
                        if (totalEl) totalEl.textContent = 'Total: ' + formatCurrency(data.cart_total || 0);
                        showAjaxFlash(data.message || 'Cart cleared.', true);
                    });
                });
            });
        });
    })();
</script>
@stack('scripts')
</body>
</html>
