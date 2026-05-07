<!-- resources/views/patient/medicines/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow-sm">
        <div class="flex gap-6">
            <div class="w-1/3">
                <img src="{{ $medicine->image_url }}" alt="{{ $medicine->name }}" class="w-full h-56 object-cover">
                @if($medicine->images->count() > 1)
                    <div class="grid grid-cols-3 gap-2 mt-3">
                        @foreach($medicine->images as $image)
                            <img
                                src="{{ Str::startsWith($image->image_path, 'images/') ? asset($image->image_path) : route('media.public', ['path' => $image->image_path]) }}"
                                alt="{{ $medicine->name }}"
                                class="w-full h-20 object-cover rounded border {{ $image->is_thumbnail ? 'ring-2 ring-blue-500' : '' }}"
                            >
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="flex-1">
                <h1 class="text-2xl font-semibold">{{ $medicine->name }}</h1>
                <p class="text-sm text-gray-600 mt-2">{{ $medicine->category_name }}</p>
                <p class="mt-4 text-gray-700">{{ $medicine->description }}</p>

                <div class="mt-4 flex items-center gap-4">
                    <span class="text-xl font-bold">₹{{ number_format($medicine->price, 2) }}</span>
                    <span class="text-sm text-gray-500">Stock: {{ $medicine->stock_quantity }}</span>
                </div>

                <div class="mt-6">
                    <form action="{{ route('patient.cart.add') }}" method="POST" class="flex items-center gap-2 ajax-add-to-cart">
                        @csrf
                        <input type="hidden" name="medicine_id" value="{{ $medicine->id }}">
                        <input type="number" name="quantity" value="1" min="1" max="{{ $medicine->stock_quantity }}" class="w-24 border rounded px-2 py-1">
                        <button type="submit" class="ml-2 bg-blue-600 text-white px-4 py-2 rounded">Add to cart</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
