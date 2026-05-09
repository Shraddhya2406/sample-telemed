@extends('layouts.app')

@section('title', 'Prescription')

@section('content')
<div class="max-w-5xl mx-auto">
    <div id="prescription-print-toolbar" class="mb-4 flex justify-end">
        <button onclick="window.print()" class="bg-green-200 px-4 py-2 rounded hover:bg-green-200" type="button">Print Prescription</button>
    </div>

    <div class="bg-white p-6 rounded shadow print-area">
        <div class="flex flex-col md:flex-row md:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold">Sample Telemed</h1>
                <p class="text-gray-600">Dr. {{ $prescription->doctor->name }} · {{ $prescription->created_at->format('d M Y') }}</p>
            </div>
        </div>

        @if($prescription->diagnosis)
            <div class="mb-4">
                <h2 class="font-bold">Diagnosis</h2>
                <p>{{ $prescription->diagnosis }}</p>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 border">Medicine</th>
                        <th class="p-3 border">Dosage</th>
                        <th class="p-3 border">Duration</th>
                        <th class="p-3 border no-print">Buy</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prescription->items as $item)
                        <tr>
                            <td class="p-3 border">
                                <div class="font-semibold">{{ $item->medicine->name }}</div>
                                <div class="text-sm text-gray-600">{{ $item->instructions }}</div>
                            </td>
                            <td class="p-3 border">{{ $item->dosage }}</td>
                            <td class="p-3 border">{{ $item->duration }}</td>
                            <td class="p-3 border no-print">
                                <form method="POST" action="{{ route('patient.cart.add') }}">
                                    @csrf
                                    <input type="hidden" name="medicine_id" value="{{ $item->medicine_id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button class="bg-indigo-600 text-white px-3 py-2 rounded" type="submit">Add to Cart</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($prescription->notes)
            <div class="mt-4">
                <h2 class="font-bold">Notes</h2>
                <p>{{ $prescription->notes }}</p>
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
