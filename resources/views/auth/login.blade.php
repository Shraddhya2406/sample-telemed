@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="mb-6">
    <p class="text-sm font-medium text-blue-700">Welcome back</p>
    <h1 class="mt-1 text-2xl font-semibold text-slate-950">Login to NexCura</h1>
    <p class="mt-2 text-sm leading-6 text-slate-600">Access your dashboard, appointments, and care records.</p>
</div>

@if ($errors->any())
    <div class="mb-5 rounded-md border border-red-200 bg-red-50 p-3">
        <ul class="space-y-1 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('login') }}" id="loginForm" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email address</label>
        <input
            type="email"
            name="email"
            id="email"
            value="{{ old('email') }}"
            required
            autofocus
            autocomplete="email"
            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 @error('email') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
            placeholder="you@example.com"
        >
        @error('email')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
        <input
            type="password"
            name="password"
            id="password"
            required
            autocomplete="current-password"
            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 @error('password') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
            placeholder="Enter your password"
        >
        @error('password')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <label for="remember" class="flex items-center gap-2 text-sm text-slate-600">
        <input
            type="checkbox"
            name="remember"
            id="remember"
            class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-600"
        >
        Remember me
    </label>

    <button
        type="submit"
        id="submitBtn"
        class="inline-flex w-full items-center justify-center rounded-md bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
    >
        <span id="buttonText">Sign in</span>
        <span id="buttonLoader" class="hidden">
            <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </span>
    </button>
</form>

<p class="mt-5 text-center text-sm text-slate-600">
    New to NexCura?
    <a href="{{ route('register') }}" class="font-semibold text-blue-700 transition hover:text-blue-800">
        Create an account
    </a>
</p>

<script>
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        const btnText = document.getElementById('buttonText');
        const btnLoader = document.getElementById('buttonLoader');

        btn.disabled = true;
        btnText.classList.add('hidden');
        btnLoader.classList.remove('hidden');
    });
</script>
@endsection
