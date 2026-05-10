@extends('layouts.patient')

@section('title', 'Medicines')
@section('page_title', 'Medicine Store')
@section('eyebrow', 'Trusted pharmacy')

@section('content')
<div class="space-y-6 pb-20 lg:pb-0">
    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-950 dark:text-white">Find medicines with confidence</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Search, compare, and add essentials to your cart. Prescription items are clearly marked when required.</p>
            </div>
            <a href="{{ route('patient.cart.index') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">View Cart</a>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-[1fr_auto_auto]">
            <label class="relative block">
                <span class="sr-only">Search medicines</span>
                <input id="medicine-search" type="search" placeholder="Search by medicine or category" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:focus:bg-slate-900">
            </label>
            <select id="category-filter" class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                <option value="">All categories</option>
                @foreach($medicines->pluck('category_name')->filter()->unique()->sort() as $category)
                    <option value="{{ Str::lower($category) }}">{{ $category }}</option>
                @endforeach
            </select>
            <select id="price-filter" class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                <option value="">Any price</option>
                <option value="0-100">Under Rs. 100</option>
                <option value="100-300">Rs. 100 - Rs. 300</option>
                <option value="300-999999">Above Rs. 300</option>
            </select>
        </div>
    </section>

    <section id="medicine-grid" class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @forelse($medicines as $medicine)
            @php
                $category = $medicine->category_name ?? 'General care';
                $requiresPrescription = (bool)($medicine->requires_prescription ?? $medicine->prescription_required ?? false);
            @endphp
            <article class="medicine-card group flex h-full flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
                data-name="{{ Str::lower($medicine->name) }}"
                data-category="{{ Str::lower($category) }}"
                data-price="{{ (float) $medicine->price }}">
                <a href="{{ route('patient.medicines.show', $medicine) }}" class="block bg-slate-100 dark:bg-slate-950">
                    <img src="{{ $medicine->image_url }}" alt="{{ $medicine->name }}" class="h-44 w-full object-cover transition duration-300 group-hover:scale-[1.02]">
                </a>
                <div class="flex flex-1 flex-col p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-bold leading-tight text-slate-950 dark:text-white">{{ $medicine->name }}</h3>
                            <p class="mt-1 text-sm font-medium text-slate-500">{{ $category }}</p>
                        </div>
                        @if($requiresPrescription)
                            <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:ring-amber-900">Rx</span>
                        @endif
                    </div>
                    <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ Str::limit($medicine->description, 110) }}</p>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-xl font-bold text-slate-950 dark:text-white">Rs. {{ number_format($medicine->price, 2) }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $medicine->stock_quantity }} in stock</span>
                    </div>
                    <form action="{{ route('patient.cart.add') }}" method="POST" class="ajax-add-to-cart mt-5 flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="medicine_id" value="{{ $medicine->id }}">
                        <input type="number" name="quantity" value="1" min="1" max="{{ $medicine->stock_quantity }}" class="h-11 w-20 rounded-lg border border-slate-200 px-3 text-sm outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950">
                        <button type="submit" class="inline-flex h-11 flex-1 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60" @disabled($medicine->stock_quantity < 1)>Add to Cart</button>
                    </form>
                    <a href="{{ route('patient.medicines.show', $medicine) }}" class="mt-3 text-sm font-bold text-blue-700 dark:text-blue-300">View details</a>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-lg border border-slate-200 bg-white p-10 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-bold text-slate-950 dark:text-white">No medicines available</h3>
                <p class="mt-2 text-sm text-slate-500">Please check again later.</p>
            </div>
        @endforelse
    </section>

    <div class="rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-slate-900">
        {{ $medicines->links() }}
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const search = document.getElementById('medicine-search');
        const category = document.getElementById('category-filter');
        const price = document.getElementById('price-filter');
        const cards = Array.from(document.querySelectorAll('.medicine-card'));

        function applyFilters() {
            const query = (search.value || '').toLowerCase().trim();
            const selectedCategory = category.value;
            const selectedPrice = price.value;

            cards.forEach((card) => {
                const cardPrice = Number(card.dataset.price || 0);
                const matchesSearch = !query || card.dataset.name.includes(query) || card.dataset.category.includes(query);
                const matchesCategory = !selectedCategory || card.dataset.category === selectedCategory;
                let matchesPrice = true;
                if (selectedPrice) {
                    const range = selectedPrice.split('-').map(Number);
                    matchesPrice = cardPrice >= range[0] && cardPrice <= range[1];
                }
                card.classList.toggle('hidden', !(matchesSearch && matchesCategory && matchesPrice));
            });
        }

        [search, category, price].forEach((el) => el.addEventListener('input', applyFilters));
    });
</script>
@endpush
@endsection
