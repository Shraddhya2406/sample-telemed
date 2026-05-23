@props([
    'title' => 'Nothing here yet',
    'message' => 'New activity will appear here as soon as it is available.',
    'icon' => 'inbox',
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 px-6 py-10 text-center']) }}>
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm ring-1 ring-slate-200">
        <i data-lucide="{{ $icon }}" class="h-5 w-5"></i>
    </div>
    <h3 class="mt-4 text-sm font-semibold text-slate-950">{{ $title }}</h3>
    <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">{{ $message }}</p>
</div>
