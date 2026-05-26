<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NexCura') }} - Intelligent Care. Human Touch.</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-slate-50 text-slate-950 antialiased">
    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur">
        <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="{{ config('app.name', 'NexCura') }} home">
                <x-logo size="34" :showText="false" />
                <span class="text-lg font-semibold tracking-normal text-slate-950">{{ config('app.name', 'NexCura') }}</span>
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="rounded-md px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-slate-950">
                    Login
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                        Sign Up
                    </a>
                @endif
            </div>
        </nav>
    </header>

    <main>
        <section class="border-b border-slate-200 bg-white">
            <div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-7xl items-center gap-10 px-4 py-10 sm:px-6 lg:grid-cols-[1.02fr_0.98fr] lg:px-8 lg:py-12">
                <div class="max-w-2xl">
                    <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-800">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Intelligent Care. Human Touch.
                    </div>

                    <h1 class="text-4xl font-semibold leading-tight tracking-normal text-slate-950 sm:text-5xl lg:text-6xl">
                        Healthcare access, organized in one calm workspace.
                    </h1>

                    <p class="mt-5 max-w-xl text-base leading-7 text-slate-600 sm:text-lg">
                        Book consultations, manage prescriptions, review health guidance, and keep care workflows moving with a clean telemedicine experience.
                    </p>

                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-md bg-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800">
                                Create Account
                            </a>
                        @endif
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-800 transition hover:border-slate-400 hover:bg-slate-50">
                            Login to Dashboard
                        </a>
                    </div>

                    <dl class="mt-9 grid max-w-xl grid-cols-3 divide-x divide-slate-200 rounded-lg border border-slate-200 bg-slate-50">
                        <div class="px-4 py-3">
                            <dt class="text-xs font-medium uppercase text-slate-500">Access</dt>
                            <dd class="mt-1 text-lg font-semibold text-slate-950">24/7</dd>
                        </div>
                        <div class="px-4 py-3">
                            <dt class="text-xs font-medium uppercase text-slate-500">Care</dt>
                            <dd class="mt-1 text-lg font-semibold text-slate-950">Online</dd>
                        </div>
                        <div class="px-4 py-3">
                            <dt class="text-xs font-medium uppercase text-slate-500">Records</dt>
                            <dd class="mt-1 text-lg font-semibold text-slate-950">Secure</dd>
                        </div>
                    </dl>
                </div>

                <div class="relative">
                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xl shadow-slate-200/70">
                        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4">
                            <div>
                                <p class="text-xs font-medium uppercase text-slate-500">Live Care Desk</p>
                                <h2 class="mt-1 text-base font-semibold text-slate-950">Today's patient flow</h2>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Active</span>
                        </div>

                        <div class="grid gap-0 md:grid-cols-[0.82fr_1.18fr]">
                            <aside class="border-b border-slate-200 bg-slate-950 p-5 text-white md:border-b-0 md:border-r">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-md bg-blue-500 text-sm font-bold">NC</div>
                                    <div>
                                        <p class="text-sm font-semibold">Care Team</p>
                                        <p class="text-xs text-slate-300">4 clinicians online</p>
                                    </div>
                                </div>

                                <div class="mt-6 space-y-3">
                                    <div class="rounded-md bg-white/10 p-3">
                                        <p class="text-xs text-slate-300">Next consult</p>
                                        <p class="mt-1 text-sm font-semibold">Dr. Mehta - 11:30 AM</p>
                                    </div>
                                    <div class="rounded-md bg-white/10 p-3">
                                        <p class="text-xs text-slate-300">Open requests</p>
                                        <p class="mt-1 text-sm font-semibold">8 appointments</p>
                                    </div>
                                </div>
                            </aside>

                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="rounded-md border border-slate-200 bg-blue-50 p-3">
                                        <p class="text-xs font-medium text-blue-700">Bookings</p>
                                        <p class="mt-2 text-2xl font-semibold text-slate-950">18</p>
                                    </div>
                                    <div class="rounded-md border border-slate-200 bg-emerald-50 p-3">
                                        <p class="text-xs font-medium text-emerald-700">Reports</p>
                                        <p class="mt-2 text-2xl font-semibold text-slate-950">12</p>
                                    </div>
                                    <div class="rounded-md border border-slate-200 bg-rose-50 p-3">
                                        <p class="text-xs font-medium text-rose-700">Alerts</p>
                                        <p class="mt-2 text-2xl font-semibold text-slate-950">3</p>
                                    </div>
                                </div>

                                <div class="mt-5 space-y-3">
                                    <div class="flex items-center justify-between rounded-md border border-slate-200 p-3">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-950">Video consultation</p>
                                            <p class="text-xs text-slate-500">Patient waiting room ready</p>
                                        </div>
                                        <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">Queued</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-md border border-slate-200 p-3">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-950">Prescription review</p>
                                            <p class="text-xs text-slate-500">Medicines and notes synced</p>
                                        </div>
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Ready</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-md border border-slate-200 p-3">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-950">Health assessment</p>
                                            <p class="text-xs text-slate-500">Guided symptom intake</p>
                                        </div>
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">New</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-slate-50 py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-4 md:grid-cols-3">
                    <article class="rounded-lg border border-slate-200 bg-white p-5">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-md bg-blue-100 text-blue-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.55-2.28A1 1 0 0121 8.62v6.76a1 1 0 01-1.45.9L15 14M5 6h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-slate-950">Consult online</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Schedule appointments and connect with doctors from a secure patient dashboard.</p>
                    </article>

                    <article class="rounded-lg border border-slate-200 bg-white p-5">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-md bg-emerald-100 text-emerald-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-5M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-slate-950">Track care details</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Keep appointments, prescriptions, orders, and messages in one focused place.</p>
                    </article>

                    <article class="rounded-lg border border-slate-200 bg-white p-5">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-md bg-rose-100 text-rose-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-slate-950">Move faster</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">A compact workflow helps patients and providers get to the next step quickly.</p>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-5 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'NexCura') }}. All rights reserved.</p>
            <p class="max-w-2xl">{{ config('app.name', 'NexCura') }} supports care coordination and guidance. Always consult a qualified healthcare professional for diagnosis and treatment.</p>
        </div>
    </footer>
</body>
</html>
