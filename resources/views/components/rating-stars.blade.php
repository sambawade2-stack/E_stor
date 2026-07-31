@props(['rating' => 0, 'size' => 'h-4 w-4'])

@php $rounded = (int) round((float) $rating); @endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5']) }}>
    @for ($i = 1; $i <= 5; $i++)
        <svg class="{{ $size }} {{ $i <= $rounded ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 0 0 .95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 0 0-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.539 1.118l-3.367-2.446a1 1 0 0 0-1.175 0l-3.367 2.446c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 0 0-.364-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 0 0 .95-.69l1.286-3.958Z"/>
        </svg>
    @endfor
</span>
