@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white p-6 rounded shadow mb-6">
        <h2 class="text-3xl font-bold mb-4">Welcome to Your Dashboard, {{ Auth::user()->name }}</h2>
        <p class="text-gray-600">Manage your health and appointments from here.</p>
    </div>

    <!-- Health Quiz Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Health Assessment Quiz Card -->
        <div class="bg-blue-50 p-6 rounded shadow border-l-4 border-blue-600">
            <h3 class="text-xl font-bold mb-2">Health Assessment Diagnosis</h3>
            <p class="text-gray-600 mb-4">Take our health assessment diagnosis to get personalized medicine recommendations based on your symptoms.</p>
            <a href="{{ route('patient.health-quiz') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Start Diagnosis
            </a>
        </div>

        <!-- Medicine Store Card -->
        <div class="bg-indigo-50 p-6 rounded shadow border-l-4 border-indigo-600">
            <h3 class="text-xl font-bold mb-2">Medicine Store</h3>
            <p class="text-gray-600 mb-4">Browse and purchase medicines online from our integrated pharmacy.</p>
            <a href="{{ route('patient.medicines.index') }}" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                Visit Store
            </a>
        </div>

        <!-- Orders Card -->
        <div class="bg-teal-50 p-6 rounded shadow border-l-4 border-teal-600">
            <h3 class="text-xl font-bold mb-2">My Orders</h3>
            <p class="text-gray-600 mb-4">View your past medicine orders, statuses, and order details.</p>
            <a href="{{ url('/patient/orders') }}" class="inline-block bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">
                View Orders
            </a>
        </div>

        <!-- Appointments Card -->
        <!--
        <div class="bg-green-50 p-6 rounded shadow border-l-4 border-green-600">
            <h3 class="text-xl font-bold mb-2">Appointments</h3>
            <p class="text-gray-600 mb-4">View, manage, and schedule your appointments with doctors.</p>
            <button disabled class="inline-block bg-gray-400 text-white px-4 py-2 rounded cursor-not-allowed" title="Coming Soon">
                View Appointments
            </button>
        </div>
        -->
        <!-- Medical History Card -->
        <!--
        <div class="bg-purple-50 p-6 rounded shadow border-l-4 border-purple-600">
            <h3 class="text-xl font-bold mb-2">Medical History</h3>
            <p class="text-gray-600 mb-4">Access your past prescriptions and medical records.</p>
            <button disabled class="inline-block bg-gray-400 text-white px-4 py-2 rounded cursor-not-allowed" title="Coming Soon">
                View History
            </button>
        </div>
        -->
        <!-- Quick Tips Card -->
        <div class="bg-yellow-50 p-6 rounded shadow border-l-4 border-yellow-600">
            <h3 class="text-xl font-bold mb-2">Health Tips</h3>
            <p class="text-gray-600 mb-4">Get daily health tips and wellness advice.</p>
            <a href="{{ url('/patient/health-tips') }}" class="inline-block bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                Read Tips
            </a>
        </div>
    </div>

    <!-- Disclaimer Section -->
    <div class="mt-6 bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded">
        <p class="text-yellow-800 text-sm">
            <strong>⚠️ Disclaimer:</strong> The health assessment provides medicine recommendations, not a medical diagnosis. 
            Please consult with a qualified healthcare professional for proper diagnosis and treatment.
        </p>
    </div>
</div>
@endsection