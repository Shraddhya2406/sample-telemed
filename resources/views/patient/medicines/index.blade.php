<!-- resources/views/patient/medicines/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">Medicine Store</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($medicines as $medicine)
            <div class="border rounded p-4 bg-white shadow-sm">
                <img src="{{ $medicine->image_url }}" alt="{{ $medicine->name }}" class="h-32 w-full object-cover mb-2">

                <h2 class="font-medium">{{ $medicine->name }}</h2>
                <p class="text-xs text-gray-500 mt-1">{{ $medicine->category_name }}</p>
                <p class="text-sm text-gray-600">{{ Str::limit($medicine->description, 80) }}</p>
                <div class="mt-2 flex items-center justify-between">
                    <span class="font-semibold">₹{{ number_format($medicine->price, 2) }}</span>
                    <span class="text-xs text-gray-500">Stock: {{ $medicine->stock_quantity }}</span>
                </div>

                <div class="mt-3">
                    <form action="{{ route('patient.cart.add') }}" method="POST" class="flex items-center gap-2 ajax-add-to-cart">
                        @csrf
                        <input type="hidden" name="medicine_id" value="{{ $medicine->id }}">
                        <input type="number" name="quantity" value="1" min="1" max="{{ $medicine->stock_quantity }}" class="w-20 border rounded px-2 py-1">
                        <button type="submit" class="ml-auto bg-blue-600 text-white px-3 py-1 rounded text-sm">Add</button>
                    </form>
                </div>

                <a href="{{ route('patient.medicines.show', $medicine) }}" class="block mt-2 text-xs text-blue-600">View details</a>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $medicines->links() }}
    </div>
</div>
@endsection
