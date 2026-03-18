@props(['size' => 40, 'class' => '', 'showText' => true])

@php
    // unique id for SVG gradient to avoid collisions when the component is rendered multiple times
    $gradientId = 'tmGradient' . uniqid();
    $titleId = 'logoTitle' . uniqid();
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 ' . $class]) }}>
    <svg
        width="{{ $size }}"
        height="{{ $size }}"
        viewBox="0 0 64 64"
        xmlns="http://www.w3.org/2000/svg"
        role="img"
        aria-labelledby="{{ $titleId }}"
        class="block"
    >
        <title id="{{ $titleId }}">Sample TeleMed logo</title>
        <defs>
            <linearGradient id="{{ $gradientId }}" x1="0%" x2="100%" y1="0%" y2="100%">
                <stop offset="0%" stop-color="#2563EB" />
                <stop offset="100%" stop-color="#10B981" />
            </linearGradient>
        </defs>

        <!-- chat bubble + medical cross icon -->
        <g transform="translate(4,4)">
            <!-- bubble circle -->
            <path d="M28 0C12.536 0 0 10.745 0 24c0 5.76 2.192 11.02 5.84 15.064L4 40l2.896-1.392C10.24 41.088 18.000 44 28 44c15.464 0 28-10.745 28-24S43.464 0 28 0z" fill="url(#{{ $gradientId }})" opacity="0.95" />

            <!-- white cross -->
            <rect x="18" y="12" width="8" height="20" rx="1.5" fill="#FFFFFF" />
            <rect x="12" y="18" width="20" height="8" rx="1.5" fill="#FFFFFF" />

            <!-- pulse line (accent) -->
            <path d="M6 34c6-6 10-10 14-6l2 4 4-8 6 8" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.85" />
        </g>
    </svg>

    @if($showText)
        <span class="font-semibold text-lg text-slate-900">TeleMed</span>
    @endif
</div>
