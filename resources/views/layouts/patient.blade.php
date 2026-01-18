<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Patient Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 text-gray-800">
    <header class="bg-blue-600 text-white p-4 flex justify-between items-center">
        <h1 class="text-lg font-bold">Sample Telemed</h1>
        <div class="flex items-center space-x-4">
            <span>Welcome, {{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="bg-red-500 px-3 py-1 rounded text-white">Logout</button>
            </form>
        </div>
    </header>

    <main class="container mx-auto p-4">
        @yield('content')
    </main>
</body>
</html>