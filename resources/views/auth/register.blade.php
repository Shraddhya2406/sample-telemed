@extends('layouts.auth')

@section('title', 'Sign Up')

@section('content')
<!-- Registration Form -->
<div class="text-center mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Your Account</h1>
    <p class="text-gray-600">Join Sample Telemed and start your health journey</p>
</div>

<!-- Validation Errors -->
@if ($errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<!-- Registration Form -->
<form method="POST" action="{{ route('register') }}" id="registerForm">
    @csrf

    <!-- Name Field -->
    <div class="mb-6">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            Full Name
        </label>
        <input 
            type="text" 
            name="name" 
            id="name" 
            value="{{ old('name') }}"
            required 
            autofocus
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none @error('name') border-red-500 @enderror"
            placeholder="John Doe"
        >
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Email Field -->
    <div class="mb-6">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
            Email Address
        </label>
        <input 
            type="email" 
            name="email" 
            id="email" 
            value="{{ old('email') }}"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none @error('email') border-red-500 @enderror"
            placeholder="you@example.com"
        >
        @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Password Field -->
    <div class="mb-6">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Password
        </label>
        <input 
            type="password" 
            name="password" 
            id="password" 
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none @error('password') border-red-500 @enderror"
            placeholder="Minimum 8 characters"
        >
        @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Confirm Password Field -->
    <div class="mb-6">
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
            Confirm Password
        </label>
        <input 
            type="password" 
            name="password_confirmation" 
            id="password_confirmation" 
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none"
            placeholder="Re-enter your password"
        >
    </div>

    <!-- Role Selection -->
    <div class="mb-6">
        <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">
            I am a
        </label>
        <select 
            name="role_id" 
            id="role_id" 
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none bg-white @error('role_id') border-red-500 @enderror"
        >
            <option value="">Select your role</option>
            <option value="2" {{ old('role_id') == '2' ? 'selected' : '' }}>Doctor</option>
            <option value="3" {{ old('role_id') == '3' ? 'selected' : '' }}>Patient</option>
        </select>
        @error('role_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Submit Button -->
    <button 
        type="submit" 
        id="submitBtn"
        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-semibold shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
    >
        <span id="buttonText">Create Account</span>
        <span id="buttonLoader" class="hidden">
            <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </span>
    </button>
</form>

<!-- Login Link -->
<div class="mt-6 text-center">
    <p class="text-sm text-gray-600">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700 transition-colors">
            Sign in here
        </a>
    </p>
</div>

<!-- Basic Loading Script -->
<script>
    document.getElementById('registerForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        const btnText = document.getElementById('buttonText');
        const btnLoader = document.getElementById('buttonLoader');
        
        btn.disabled = true;
        btnText.classList.add('hidden');
        btnLoader.classList.remove('hidden');
    });
</script>
@endsection
