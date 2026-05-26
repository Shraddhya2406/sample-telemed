@extends('layouts.auth')

@section('title', 'Sign Up')

@section('content')
<div class="mb-5">
    <p class="text-sm font-medium text-blue-700">Get started</p>
    <h1 class="mt-1 text-2xl font-semibold text-slate-950">Create your account</h1>
    <p class="mt-2 text-sm leading-6 text-slate-600">Join {{ config('app.name', 'NexCura') }} as a patient or doctor and continue to your dashboard.</p>
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
            <div class="mb-1.5 flex items-center gap-2">
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                <span class="relative inline-flex">
                    <button
                        type="button"
                        id="passwordRulesToggle"
                        class="flex h-4 w-4 items-center justify-center rounded-full border border-slate-300 text-[10px] font-semibold leading-none text-slate-500 transition hover:border-blue-400 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        aria-label="Password requirements"
                        aria-controls="passwordRules"
                        aria-expanded="false"
                        title="Password must include uppercase, lowercase, number, special character, 8+ characters, and no spaces."
                    >
                        i
                    </button>
                    <div id="passwordRules" class="pointer-events-none absolute bottom-[calc(100%+0.5rem)] left-1/2 z-20 hidden w-56 -translate-x-1/2 gap-1.5 rounded-md border border-red-200 bg-red-50 p-2 text-xs text-red-700 shadow-lg shadow-slate-900/10 sm:grid-cols-2">
                        <span class="hidden items-center gap-1.5" data-password-rule="length"><span class="h-1.5 w-1.5 rounded-full"></span>8+ characters</span>
                        <span class="hidden items-center gap-1.5" data-password-rule="upper"><span class="h-1.5 w-1.5 rounded-full"></span>Uppercase</span>
                        <span class="hidden items-center gap-1.5" data-password-rule="lower"><span class="h-1.5 w-1.5 rounded-full"></span>Lowercase</span>
                        <span class="hidden items-center gap-1.5" data-password-rule="number"><span class="h-1.5 w-1.5 rounded-full"></span>Number</span>
                        <span class="hidden items-center gap-1.5" data-password-rule="special"><span class="h-1.5 w-1.5 rounded-full"></span>Special char</span>
                        <span class="hidden items-center gap-1.5" data-password-rule="spaces"><span class="h-1.5 w-1.5 rounded-full"></span>No spaces</span>
                    </div>
                </span>
            </div>
            <input
                type="password"
                name="password"
                id="password"
                required
                minlength="8"
                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])\S{8,}"
                title="Use 8+ characters with uppercase, lowercase, number, special character, and no spaces."
                autocomplete="new-password"
                aria-describedby="passwordRules"
                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 @error('password') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                placeholder="Strong password"
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
            <p id="passwordMatch" class="mt-1.5 hidden text-xs text-red-600">Passwords must match.</p>
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
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    const passwordMatch = document.getElementById('passwordMatch');
    const passwordRules = document.getElementById('passwordRules');
    const passwordRulesToggle = document.getElementById('passwordRulesToggle');
    let passwordRulesManuallyOpen = false;
    const passwordChecks = {
        length: (value) => value.length >= 8,
        upper: (value) => /[A-Z]/.test(value),
        lower: (value) => /[a-z]/.test(value),
        number: (value) => /\d/.test(value),
        special: (value) => /[\W_]/.test(value),
        spaces: (value) => !/\s/.test(value),
    };

    function updatePasswordRules() {
        const value = password.value;
        let missingRules = 0;

        Object.entries(passwordChecks).forEach(([rule, passes]) => {
            const item = document.querySelector(`[data-password-rule="${rule}"]`);
            const dot = item.querySelector('span');
            const isValid = passes(value);
            const shouldShowRule = passwordRulesManuallyOpen || !isValid;

            item.classList.toggle('hidden', !shouldShowRule);
            item.classList.toggle('flex', shouldShowRule);
            item.classList.toggle('text-emerald-700', isValid);
            item.classList.toggle('text-red-700', !isValid);
            dot.classList.toggle('bg-emerald-500', isValid);
            dot.classList.toggle('bg-red-500', !isValid);

            if (!isValid) {
                missingRules += 1;
            }
        });

        const shouldShowRules = passwordRulesManuallyOpen || (value !== '' && missingRules > 0);

        passwordRules.classList.toggle('hidden', !shouldShowRules);
        passwordRules.classList.toggle('grid', shouldShowRules);
        passwordRules.classList.toggle('border-red-200', missingRules > 0);
        passwordRules.classList.toggle('bg-red-50', missingRules > 0);
        passwordRules.classList.toggle('text-red-700', missingRules > 0);
        passwordRules.classList.toggle('border-emerald-200', missingRules === 0);
        passwordRules.classList.toggle('bg-emerald-50', missingRules === 0);
        passwordRules.classList.toggle('text-emerald-700', missingRules === 0);
        passwordRulesToggle.setAttribute('aria-expanded', shouldShowRules ? 'true' : 'false');
    }

    function updatePasswordMatch() {
        const hasMismatch = passwordConfirmation.value !== '' && password.value !== passwordConfirmation.value;

        passwordConfirmation.setCustomValidity(hasMismatch ? 'Passwords must match.' : '');
        passwordMatch.classList.toggle('hidden', !hasMismatch);
    }

    password.addEventListener('input', function () {
        updatePasswordRules();
        updatePasswordMatch();
    });

    passwordConfirmation.addEventListener('input', updatePasswordMatch);
    passwordRulesToggle.addEventListener('click', function () {
        passwordRulesManuallyOpen = passwordRules.classList.contains('hidden');
        updatePasswordRules();
    });
    updatePasswordRules();

    form.addEventListener('submit', function () {
        if (!form.checkValidity()) {
            return;
        }

        const btn = document.getElementById('submitBtn');
        const btnText = document.getElementById('buttonText');
        const btnLoader = document.getElementById('buttonLoader');

        btn.disabled = true;
        btnText.classList.add('hidden');
        btnLoader.classList.remove('hidden');
    });
</script>
@endsection
