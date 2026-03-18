<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sample Telemed') - {{ config('app.name', 'Laravel') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gradient-to-br from-blue-50 via-teal-50 to-green-50 min-h-screen">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, rgb(59, 130, 246) 1px, transparent 0); background-size: 40px 40px;"></div>
    </div>

    <!-- Main Content -->
    <div class="relative min-h-screen flex items-center justify-center px-4 py-12">
        <!-- Logo Section -->
        <div class="absolute top-6 left-6" style="top: 6px;">
            <a href="{{ url('/') }}" class="flex items-center space-x-2 text-blue-600 hover:text-blue-700 transition-colors">
                <x-logo size="32" :showText="false" class="block" />
                <span class="text-xl font-bold">{{ config('app.name', 'Sample Telemed') }}</span>
            </a>
        </div>

        <!-- Auth Card -->
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-10 border border-gray-100">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
