<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sample Telemed - Online Doctor Consultation Made Simple</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-white">
    <!-- Header Navigation -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-100 shadow-sm">
        <nav class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    <span class="text-xl font-bold text-gray-900">Sample Telemed</span>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard.patient') }}" class="text-gray-700 hover:text-blue-600 px-4 py-2 rounded-lg transition-colors font-medium">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 px-4 py-2 rounded-lg transition-colors font-medium">
                            Login
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md">
                                Sign Up
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-blue-50 via-teal-50 to-green-50 py-20 md:py-32">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6 mt-4 leading-tight">
                    Online Doctor Consultation<br />
                    <span class="text-blue-600">Made Simple</span>
                </h1>
                <p class="text-xl md:text-2xl text-gray-600 mb-8 max-w-2xl mx-auto leading-relaxed">
                    Answer a quick health diagnosis and get personalized medicine recommendations based on your symptoms.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @guest
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-8 py-4 rounded-lg hover:bg-blue-700 transition-colors font-semibold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            Get Started
                        </a>
                        <a href="{{ route('login') }}" class="bg-white text-blue-600 px-8 py-4 rounded-lg border-2 border-blue-600 hover:bg-blue-50 transition-colors font-semibold text-lg shadow-md">
                            Login
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-900 mb-12 mt-4 mb-4">
                Why Choose Sample Telemed?
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Feature 1: Online Consultation -->
                <div class="bg-gradient-to-br from-blue-50 to-teal-50 p-6 rounded-2xl border border-blue-100 hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Online Consultation</h3>
                    <p class="text-gray-600">Connect with qualified doctors from the comfort of your home.</p>
                </div>

                <!-- Feature 2: Health Assessment Quiz -->
                <div class="bg-gradient-to-br from-teal-50 to-green-50 p-6 rounded-2xl border border-teal-100 hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Health Assessment Quiz</h3>
                    <p class="text-gray-600">Quick symptom assessment to understand your health needs.</p>
                </div>

                <!-- Feature 3: Medicine Recommendation -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-2xl border border-green-100 hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Medicine Recommendation</h3>
                    <p class="text-gray-600">Get personalized medicine suggestions based on your assessment.</p>
                </div>

                <!-- Feature 4: Secure & Private -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-2xl border border-blue-100 hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-amber-200 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Secure & Private</h3>
                    <p class="text-gray-600">Your health information is protected with industry-standard security.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-20 bg-gradient-to-br from-blue-50 via-teal-50 to-green-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-900 mb-4 mt-4">
                How It Works
            </h2>
            <div class="max-w-4xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Step 1 -->
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-white text-2xl font-bold shadow-lg">
                            1
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Sign Up as a Patient</h3>
                        <p class="text-gray-600">Create your account in seconds with just your email and basic information.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="text-center">
                        <div class="w-16 h-16 bg-rose-600 rounded-full flex items-center justify-center mx-auto mb-4 text-white text-2xl font-bold shadow-lg">
                            2
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Take Health Quiz</h3>
                        <p class="text-gray-600">Answer a few simple questions about your symptoms and health condition.</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-white text-2xl font-bold shadow-lg">
                            3
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Get Medicine & Consult Doctor</h3>
                        <p class="text-gray-600">Receive personalized recommendations and connect with a doctor if needed.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                <div class="mb-6">
                    <div class="flex items-center justify-center space-x-2 mb-4 mt-4 bg-amber-200">
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        <span class="text-xl font-bold text-white">Sample Telemed</span>
                    </div>
                </div>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg mb-6 max-w-2xl mx-auto">
                    <p class="text-yellow-800 text-sm">
                        <strong>⚠️ Disclaimer:</strong> This platform provides medicine recommendations, not medical diagnoses. 
                        Please consult with a qualified healthcare professional for proper diagnosis and treatment.
                    </p>
                </div>
                <p class="text-sm text-gray-400">
                    &copy; {{ date('Y') }} Sample Telemed. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
