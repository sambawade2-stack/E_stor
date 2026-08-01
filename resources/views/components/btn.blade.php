@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'submit',
    'disabled' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-full font-semibold transition duration-300 active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50';

    $variants = [
        'primary' => 'bg-gradient-to-r from-primary-600 to-accent-500 text-white shadow-lg shadow-primary-600/30 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-primary-500/40 active:translate-y-0',
        'secondary' => 'bg-gray-950 text-white hover:-translate-y-0.5 hover:bg-gray-800 hover:shadow-lg hover:shadow-gray-950/30 active:translate-y-0',
        'outline' => 'border-2 border-gray-200 text-gray-700 hover:-translate-y-0.5 hover:border-primary-300 hover:text-primary-600 active:translate-y-0',
        'ghost' => 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
        'danger' => 'bg-danger-600 text-white shadow-lg shadow-danger-600/25 hover:-translate-y-0.5 hover:bg-danger-500 active:translate-y-0',
    ];

    $sizes = [
        'sm' => 'px-4 py-2 text-xs',
        'md' => 'px-6 py-3 text-sm',
        'lg' => 'px-8 py-3.5 text-base',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
