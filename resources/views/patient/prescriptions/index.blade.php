@extends('layouts.patient')

@section('title', 'My Prescriptions')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white p-6 rounded shadow mb-6 dark:bg-slate-900 dark:border dark:border-slate-800">
        <h1 class="text-2xl font-bold text-slate-950 dark:text-white">My Prescriptions</h1>
        <p class="text-gray-600 dark:text-slate-300">Prescriptions shared by your doctors.</p>
    </div>

    <div class="space-y-4">
        @forelse($prescriptions as $prescription)
            <div class="bg-white p-5 rounded shadow dark:bg-slate-900 dark:border dark:border-slate-800">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                    <div>
                        <div class="font-bold text-slate-950 dark:text-white">Dr. {{ $prescription->doctor->name }}</div>
                        <div class="text-sm text-gray-600 dark:text-slate-400">{{ $prescription->created_at->format('d M Y') }}</div>
                        <div class="mt-2 text-slate-700 dark:text-slate-200">{{ $prescription->diagnosis ?: $prescription->notes }}</div>
                    </div>
                    <a href="{{ route('patient.prescriptions.show', $prescription) }}" class="inline-block bg-teal-600 text-white px-4 py-2 rounded">View</a>
                </div>
            </div>
        @empty
            <div class="bg-white p-6 rounded shadow text-gray-600 dark:bg-slate-900 dark:text-slate-400 dark:border dark:border-slate-800">No prescriptions yet.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $prescriptions->links() }}</div>
</div>
@endsection
