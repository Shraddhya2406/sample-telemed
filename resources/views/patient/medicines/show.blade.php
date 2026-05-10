@extends('layouts.patient')

@section('title', $medicine->name)
@section('page_title', 'Medicine Details')
@section('eyebrow', 'Trusted pharmacy')

@section('content')
@php
    $requiresPrescription = (bool)($medicine->requires_prescription ?? $medicine->prescription_required ?? false);
@endphp

<div class="mx-auto max-w-5xl pb-20 lg:pb-0">
    <a href="{{ route('patient.medicines.index') }}" class="mb-5 inline-flex text-sm font-bold text-blue-700 dark:text-blue-300">Back to medicines</a>
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-8 p-6 lg:grid-cols-[.85fr_1.15fr] lg:p-8">
            <div>
                <img src="{{ $medicine->image_url }}" alt="{{ $medicine->name }}" class="aspect-square w-full rounded-lg bg-slate-100 object-cover dark:bg-slate-950">
                @if($medicine->images->count() > 1)
                    <div class="mt-3 grid grid-cols-4 gap-2">
                        @foreach($medicine->images as $image)
                            <img
                                src="{{ Str::startsWith($image->image_path, 'images/') ? asset($image->image_path) : route('media.public', ['path' => $image->image_path]) }}"
                                alt="{{ $medicine->name }}"
                                class="aspect-square w-full rounded-lg border border-slate-200 object-cover {{ $image->is_thumbnail ? 'ring-2 ring-blue-500' : '' }} dark:border-slate-800"
                            >
                        @endforeach
                    </div>
                @endif
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-sm font-bold text-blue-700 ring-1 ring-blue-100 dark:bg-blue-950 dark:text-blue-300 dark:ring-blue-900">{{ $medicine->category_name ?? 'General care' }}</span>
                    @if($requiresPrescription)
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-sm font-bold text-amber-700 ring-1 ring-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:ring-amber-900">Prescription required</span>
                    @endif
                </div>
                <h2 class="mt-4 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $medicine->name }}</h2>
                <p class="mt-4 leading-7 text-slate-600 dark:text-slate-300">{{ $medicine->description }}</p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-950">
                        <p class="text-sm font-semibold text-slate-500">Price</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">Rs. {{ number_format($medicine->price, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-950">
                        <p class="text-sm font-semibold text-slate-500">Availability</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">{{ $medicine->stock_quantity }} in stock</p>
                    </div>
                </div>

                <form action="{{ route('patient.cart.add') }}" method="POST" class="ajax-add-to-cart mt-7 rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                    @csrf
                    <input type="hidden" name="medicine_id" value="{{ $medicine->id }}">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-200" for="quantity">Quantity</label>
                    <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                        <input id="quantity" type="number" name="quantity" value="1" min="1" max="{{ $medicine->stock_quantity }}" class="h-12 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100 sm:w-28 dark:border-slate-700 dark:bg-slate-900">
                        <button type="submit" class="inline-flex h-12 flex-1 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700" @disabled($medicine->stock_quantity < 1)>Add to Cart</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
