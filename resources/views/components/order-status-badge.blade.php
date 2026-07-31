@props(['status'])

@php
    use App\Enums\OrderStatus;

    $classes = match ($status) {
        OrderStatus::Pending => 'bg-gray-100 text-gray-700',
        OrderStatus::Processing => 'bg-blue-100 text-blue-700',
        OrderStatus::Paid => 'bg-green-100 text-green-700',
        OrderStatus::Shipped => 'bg-indigo-100 text-indigo-700',
        OrderStatus::Delivered => 'bg-emerald-100 text-emerald-700',
        OrderStatus::Cancelled => 'bg-red-100 text-red-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "rounded-full px-3 py-1 text-xs font-bold {$classes}"]) }}>
    {{ $status->label() }}
</span>
