@extends('layouts.patient')

@section('content')
<div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Quiz Result</h2>

    <div class="mb-4">
        <p class="text-lg font-semibold">Disease Category: {{ $diseaseName }}</p>
        <p class="text-lg font-semibold">Recommended Medicine: {{ $medicineName }}</p>
        <p class="text-gray-600">Advice: {{ $advice }}</p>
    </div>

    <p class="text-yellow-600 text-sm mb-4">⚠️ This is not a medical diagnosis. Consult a doctor.</p>

    <div class="flex space-x-4">
        <a href="{{ route('patient.book-appointment') }}" class="bg-green-600 text-white px-4 py-2 rounded">Book Doctor Appointment</a>
        <a href="{{ route('dashboard.patient') }}" class="bg-gray-600 text-white px-4 py-2 rounded">Go to Dashboard</a>
    </div>
</div>
@endsection