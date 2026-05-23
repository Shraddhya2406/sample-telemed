<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Doctor Panel') - {{ config('app.name', 'Sample Telemed') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        [x-cloak], .is-hidden { display: none !important; }
        @media print {
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area { position: absolute; inset: 0; width: 100%; border: 0 !important; box-shadow: none !important; }
            .no-print { display: none !important; }
        }
    </style>
    @stack('styles')
</head>
@php
    $doctor = auth()->user();
    $profile = $doctor?->doctorProfile;
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'doctor.dashboard', 'active' => 'doctor.dashboard', 'icon' => 'layout-dashboard'],
        ['label' => 'Appointments', 'route' => 'doctor.appointments.index', 'active' => 'doctor.appointments.*', 'icon' => 'calendar-check'],
        ['label' => 'Patients', 'route' => 'doctor.patients.index', 'active' => 'doctor.patients.*', 'icon' => 'users'],
        ['label' => 'Video Consultations', 'route' => 'doctor.appointments.index', 'params' => ['status' => 'approved'], 'active' => 'doctor.call.*', 'icon' => 'video'],
        ['label' => 'Prescriptions', 'route' => 'doctor.prescriptions.index', 'active' => 'doctor.prescriptions.*', 'icon' => 'clipboard-list'],
        ['label' => 'Profile Settings', 'route' => 'doctor.profile', 'active' => 'doctor.profile', 'icon' => 'settings'],
    ];
@endphp
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div class="min-h-screen lg:flex">
        <aside id="doctor-sidebar" class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-slate-200 bg-white shadow-2xl shadow-slate-950/10 transition-transform duration-300 lg:sticky lg:top-0 lg:translate-x-0 lg:shadow-none">
            <div class="flex h-full flex-col">
                <div class="flex items-center gap-3 border-b border-slate-200 p-4">
                    <x-logo size="40" :showText="false" class="block" />
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">Doctor Panel</p>
                        <p class="truncate text-base font-bold text-slate-950">{{ config('app.name', 'Sample Telemed') }}</p>
                    </div>
                    <button type="button" class="ml-auto rounded-xl p-2 text-slate-500 hover:bg-slate-100 lg:hidden" data-sidebar-close aria-label="Close menu">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-5">
                    @foreach($navItems as $item)
                        @php
                            $isActive = request()->routeIs($item['active']);
                            $href = route($item['route'], $item['params'] ?? []);
                        @endphp
                        <a href="{{ $href }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $isActive ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">
                            <i data-lucide="{{ $item['icon'] }}" class="h-5 w-5 shrink-0 {{ $isActive ? 'text-white' : 'text-slate-400 group-hover:text-blue-600' }}"></i>
                            <span class="truncate">{{ $item['label'] }}</span>
                            @if($item['label'] === 'Notifications' && $notificationUnreadCount > 0)
                                <span class="ml-auto rounded-full bg-red-500 px-2 py-0.5 text-xs text-white">{{ $notificationUnreadCount }}</span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                <div class="m-4 rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-emerald-50 p-4">
                    <p class="text-sm font-semibold text-slate-950">{{ $doctor?->name }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $profile?->specialization ?: 'Clinical specialist' }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-red-200 hover:text-red-600" type="submit">
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-950/40 lg:hidden" data-sidebar-close></div>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div class="flex min-h-20 items-center justify-between gap-4 p-3 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-600 shadow-sm lg:hidden" data-sidebar-open aria-label="Open menu">
                            <i data-lucide="menu" class="h-5 w-5"></i>
                        </button>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">@yield('kicker', 'Clinical Workspace')</p>
                            <h1 class="truncate text-xl font-bold text-slate-950 sm:text-2xl">@yield('page-title', 'Doctor Panel')</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <button type="button" class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-200 hover:text-blue-600" data-notifications-toggle aria-label="Notifications">
                                <i data-lucide="bell" class="h-5 w-5"></i>
                                @if($notificationUnreadCount > 0)
                                    <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold text-white">{{ $notificationUnreadCount }}</span>
                                @endif
                            </button>
                            <div class="is-hidden absolute right-0 mt-3 w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/10" data-notifications-menu>
                                <div class="border-b border-slate-200 px-4 py-3">
                                    <p class="font-semibold text-slate-950">Notifications</p>
                                    <p class="text-sm text-slate-500">{{ $notificationUnreadCount }} unread updates</p>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    @forelse($headerNotifications as $notification)
                                        <a href="{{ route('notifications.open', $notification) }}" class="flex gap-3 border-b border-slate-100 px-4 py-3 transition hover:bg-slate-50">
                                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $notification->read_at ? 'bg-slate-300' : 'bg-blue-600' }}"></span>
                                            <span class="min-w-0">
                                                <span class="block text-sm font-semibold text-slate-950">{{ $notification->title }}</span>
                                                @if($notification->body)
                                                    <span class="mt-0.5 block text-sm text-slate-500">{{ $notification->body }}</span>
                                                @endif
                                                <span class="mt-1 block text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                            </span>
                                        </a>
                                    @empty
                                        <div class="px-4 py-8 text-center text-sm text-slate-500">No notifications yet.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('doctor.profile') }}" class="hidden items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm transition hover:border-blue-200 sm:flex">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-sm font-bold text-white">{{ str($doctor?->name ?? 'D')->substr(0, 1)->upper() }}</span>
                            <span class="min-w-0 text-left">
                                <span class="block max-w-36 truncate text-sm font-semibold text-slate-950">{{ $doctor?->name }}</span>
                                <span class="block max-w-36 truncate text-xs text-slate-500">{{ $profile?->specialization ?: 'Doctor' }}</span>
                            </span>
                        </a>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="animate-[fadeIn_0.25s_ease-out]">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @include('call-popup')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) window.lucide.createIcons();

            const sidebar = document.getElementById('doctor-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            document.querySelectorAll('[data-sidebar-open]').forEach((button) => {
                button.addEventListener('click', () => {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                });
            });
            document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
                button.addEventListener('click', () => {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                });
            });

            const notificationsToggle = document.querySelector('[data-notifications-toggle]');
            const notificationsMenu = document.querySelector('[data-notifications-menu]');
            notificationsToggle?.addEventListener('click', (event) => {
                event.stopPropagation();
                notificationsMenu?.classList.toggle('is-hidden');
            });
            document.addEventListener('click', (event) => {
                if (!notificationsMenu || notificationsMenu.classList.contains('is-hidden')) return;
                if (!notificationsMenu.contains(event.target) && !notificationsToggle.contains(event.target)) {
                    notificationsMenu.classList.add('is-hidden');
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
