@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-gray-200 text-sm shadow-sm transition focus:border-primary-500 focus:ring-primary-500']) }}>
