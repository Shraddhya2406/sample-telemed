@extends('layouts.patient')

@section('title', 'Prescription')

@section('content')
<div class="max-w-5xl mx-auto">
    <div id="prescription-print-toolbar" class="mb-4 flex justify-end">
        <button onclick="window.print()" class="bg-green-200 px-4 py-2 rounded hover:bg-green-200 dark:bg-green-900 dark:text-green-100 dark:hover:bg-green-800" type="button">Print Prescription</button>
    </div>

    <div class="bg-white p-6 rounded shadow print-area dark:bg-slate-900 dark:text-slate-100 dark:border dark:border-slate-800">
        <div class="flex flex-col md:flex-row md:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-950 dark:text-white">Sample Telemed</h1>
                <p class="text-gray-600 dark:text-slate-300">Dr. {{ $prescription->doctor->name }} - {{ $prescription->created_at->format('d M Y') }}</p>
            </div>
        </div>

        @if($prescription->diagnosis)
            <div class="mb-4">
                <h2 class="font-bold text-slate-950 dark:text-white">Diagnosis</h2>
                <p class="text-slate-700 dark:text-slate-200">{{ $prescription->diagnosis }}</p>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left border border-slate-200 dark:border-slate-700">
                <thead class="bg-gray-50 dark:bg-slate-950">
                    <tr>
                        <th class="p-3 border border-slate-200 dark:border-slate-700">Medicine</th>
                        <th class="p-3 border border-slate-200 dark:border-slate-700">Dosage</th>
                        <th class="p-3 border border-slate-200 dark:border-slate-700">Duration</th>
                        <th class="p-3 border border-slate-200 dark:border-slate-700 no-print">Buy</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prescription->items as $item)
                        <tr class="dark:text-slate-100">
                            <td class="p-3 border border-slate-200 dark:border-slate-700">
                                <div class="font-semibold">{{ $item->medicine->name }}</div>
                                <div class="text-sm text-gray-600 dark:text-slate-400">{{ $item->instructions }}</div>
                            </td>
                            <td class="p-3 border border-slate-200 dark:border-slate-700">{{ $item->dosage }}</td>
                            <td class="p-3 border border-slate-200 dark:border-slate-700">{{ $item->duration }}</td>
                            <td class="p-3 border border-slate-200 dark:border-slate-700 no-print">
                                <form method="POST" action="{{ route('patient.cart.add') }}">
                                    @csrf
                                    <input type="hidden" name="medicine_id" value="{{ $item->medicine_id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button class="bg-indigo-600 text-white px-3 py-2 rounded hover:bg-indigo-700" type="submit">Add to Cart</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($prescription->notes)
            <div class="mt-4">
                <h2 class="font-bold text-slate-950 dark:text-white">Notes</h2>
                <p class="text-slate-700 dark:text-slate-200">{{ $prescription->notes }}</p>
            </div>
        @endif
    </div>
</div>
@if(request()->boolean('print'))
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
@endif
@endsection
