@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow-sm">
        <h1 class="text-2xl font-semibold mb-2">Health Tips</h1>
        <p class="text-gray-600 mb-4">Helpful daily tips to keep you healthy. These are general suggestions and not medical advice.</p>

        <ul class="space-y-4">
            <li class="p-4 bg-gray-50 rounded">
                <h3 class="font-semibold">Stay Hydrated</h3>
                <p class="text-sm text-gray-600">Drink enough water throughout the day — aim for at least 6-8 glasses depending on activity level.</p>
            </li>

            <li class="p-4 bg-gray-50 rounded">
                <h3 class="font-semibold">Balanced Diet</h3>
                <p class="text-sm text-gray-600">Include a variety of fruits, vegetables, whole grains, lean proteins, and healthy fats in your meals.</p>
            </li>

            <li class="p-4 bg-gray-50 rounded">
                <h3 class="font-semibold">Regular Exercise</h3>
                <p class="text-sm text-gray-600">Aim for at least 150 minutes of moderate-intensity exercise per week — walking, cycling, or swimming are great options.</p>
            </li>

            <li class="p-4 bg-gray-50 rounded">
                <h3 class="font-semibold">Sleep Well</h3>
                <p class="text-sm text-gray-600">Maintain a consistent sleep schedule and aim for 7–9 hours of sleep per night.</p>
            </li>

            <li class="p-4 bg-gray-50 rounded">
                <h3 class="font-semibold">Manage Stress</h3>
                <p class="text-sm text-gray-600">Practice relaxation techniques like deep breathing, meditation, or mindful walking to reduce stress.</p>
            </li>
        </ul>

        <div class="mt-6 flex justify-between items-center">
            <a href="{{ route('dashboard.patient') }}" class="text-sm text-blue-600">&larr; Back to dashboard</a>
            <a href="{{ route('patient.medicines.index') }}" class="bg-indigo-600 text-white px-3 py-2 rounded">Browse Medicines</a>
        </div>
    </div>
</div>
@endsection