<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NexCura') - {{ config('app.name', 'NexCura') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-slate-50 text-slate-950 antialiased">
    <header class="border-b border-slate-200 bg-white/90 backdrop-blur">
        <nav class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-2" aria-label="NexCura home">
                <x-logo size="30" :showText="false" />
                <span class="text-base font-semibold text-slate-950">{{ config('app.name', 'NexCura') }}</span>
            </a>

            <a href="{{ url('/') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">
                Home
            </a>
        </nav>
    </header>

    <main class="mx-auto grid min-h-[calc(100vh-3.5rem)] max-w-6xl items-center gap-8 px-4 py-6 sm:px-6 lg:grid-cols-[0.94fr_1.06fr] lg:px-8">
        <section class="hidden overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xl shadow-slate-200/70 lg:block">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                <p class="text-xs font-medium uppercase text-slate-500">NexCura Access</p>
                <h1 class="mt-1 text-xl font-semibold text-slate-950">Care workflows in one secure place.</h1>
            </div>

            <div class="grid grid-cols-[0.85fr_1.15fr]">
                <aside class="bg-slate-950 p-6 text-white">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-md bg-blue-500 text-sm font-bold">NC</div>
                        <div>
                            <p class="text-sm font-semibold">Care Team</p>
                            <p class="text-xs text-slate-300">Online now</p>
                        </div>
                    </div>

                    <div class="mt-7 space-y-3">
                        <div class="rounded-md bg-white/10 p-3">
                            <p class="text-xs text-slate-300">Appointments</p>
                            <p class="mt-1 text-sm font-semibold">Book and manage visits</p>
                        </div>
                        <div class="rounded-md bg-white/10 p-3">
                            <p class="text-xs text-slate-300">Prescriptions</p>
                            <p class="mt-1 text-sm font-semibold">Review care notes</p>
                        </div>
                    </div>
                </aside>

                <div class="p-6">
                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-md border border-slate-200 bg-blue-50 p-3">
                            <p class="text-xs font-medium text-blue-700">Visits</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-950">18</p>
                        </div>
                        <div class="rounded-md border border-slate-200 bg-emerald-50 p-3">
                            <p class="text-xs font-medium text-emerald-700">Records</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-950">32</p>
                        </div>
                        <div class="rounded-md border border-slate-200 bg-rose-50 p-3">
                            <p class="text-xs font-medium text-rose-700">Alerts</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-950">3</p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-md border border-slate-200 p-3">
                            <p class="text-sm font-semibold text-slate-950">Video consultation</p>
                            <p class="mt-1 text-xs text-slate-500">Secure doctor and patient calls.</p>
                        </div>
                        <div class="rounded-md border border-slate-200 p-3">
                            <p class="text-sm font-semibold text-slate-950">Health assessment</p>
                            <p class="mt-1 text-xs text-slate-500">Guided intake before consultation.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto w-full max-w-md">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/70 sm:p-6">
                @yield('content')
            </div>
        </section>
    </main>
</body>
</html>
