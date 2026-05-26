@extends('layouts.auth')

@section('title', 'Sign Up')

@section('content')
<div class="mb-5">
    <p class="text-sm font-medium text-blue-700">Get started</p>
    <h1 class="mt-1 text-2xl font-semibold text-slate-950">Create your account</h1>
    <p class="mt-2 text-sm leading-6 text-slate-600">Join NexCura as a patient or doctor and continue to your dashboard.</p>
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

<form method="POST" action="{{ route('register') }}" id="registerForm" class="space-y-4">
    @csrf

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Full name</label>
            <input
                type="text"
                name="name"
                id="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 @error('name') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                placeholder="John Doe"
            >
            @error('name')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="role_id" class="mb-1.5 block text-sm font-medium text-slate-700">I am a</label>
            <select
                name="role_id"
                id="role_id"
                required
                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-100 @error('role_id') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
            >
                <option value="">Select role</option>
                <option value="2" {{ old('role_id') == '2' ? 'selected' : '' }}>Doctor</option>
                <option value="3" {{ old('role_id') == '3' ? 'selected' : '' }}>Patient</option>
            </select>
            @error('role_id')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email address</label>
        <input
            type="email"
            name="email"
            id="email"
            value="{{ old('email') }}"
            required
            autocomplete="email"
            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 @error('email') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
            placeholder="you@example.com"
        >
        @error('email')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
            <input
                type="password"
                name="password"
                id="password"
                required
                autocomplete="new-password"
                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 @error('password') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                placeholder="Minimum 8 characters"
            >
            @error('password')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">Confirm password</label>
            <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                required
                autocomplete="new-password"
                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                placeholder="Re-enter password"
            >
        </div>
    </div>

    <button
        type="submit"
        id="submitBtn"
        class="inline-flex w-full items-center justify-center rounded-md bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
    >
        <span id="buttonText">Create account</span>
        <span id="buttonLoader" class="hidden">
            <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </span>
    </button>
</form>

<p class="mt-5 text-center text-sm text-slate-600">
    Already have an account?
    <a href="{{ route('login') }}" class="font-semibold text-blue-700 transition hover:text-blue-800">
        Sign in
    </a>
</p>

<script>
    document.getElementById('registerForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        const btnText = document.getElementById('buttonText');
        const btnLoader = document.getElementById('buttonLoader');

        btn.disabled = true;
        btnText.classList.add('hidden');
        btnLoader.classList.remove('hidden');
    });
</script>
@endsection
