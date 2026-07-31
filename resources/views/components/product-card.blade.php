@props(['product'])

<article class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white transition duration-300 hover:-translate-y-1 hover:border-primary-100 hover:shadow-xl hover:shadow-gray-200/60">

    {{-- Badges --}}
    <div class="absolute left-3 top-3 z-10 flex flex-col gap-1.5">
        @if ($product->discount_percentage)
            <span class="rounded-full bg-red-500 px-2.5 py-1 text-[11px] font-bold text-white">-{{ $product->discount_percentage }}%</span>
        @endif
        @unless ($product->inStock())
            <span class="rounded-full bg-gray-900/80 px-2.5 py-1 text-[11px] font-semibold text-white">Rupture</span>
        @endunless
    </div>

    {{-- Image --}}
    <a href="{{ route('shop.product', $product) }}" class="relative block aspect-square overflow-hidden bg-gray-50">
        <img src="{{ $product->primaryImage?->url() ?? asset('images/placeholder-product.svg') }}"
             alt="{{ $product->primaryImage?->alt ?? $product->name }}"
             loading="lazy"
             class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
    </a>

    {{-- Contenu --}}
    <div class="flex flex-1 flex-col gap-1.5 p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $product->category->name }}</p>

        <h3 class="line-clamp-2 min-h-10 text-sm font-semibold leading-snug text-gray-900">
            <a href="{{ route('shop.product', $product) }}" class="transition hover:text-primary-600">
                <span class="absolute inset-0" aria-hidden="true"></span>
                {{ $product->name }}
            </a>
        </h3>

        @if (($product->rating ?? null) !== null)
            <x-rating-stars :rating="$product->rating" size="h-3.5 w-3.5" />
        @endif

        <div class="mt-auto flex items-baseline gap-2 pt-1.5">
            <span class="text-base font-bold text-gray-900">{{ format_price($product->current_price) }}</span>
            @if ($product->isOnSale())
                <span class="text-xs text-gray-400 line-through">{{ format_price($product->price) }}</span>
            @endif
        </div>
    </div>
</article>
