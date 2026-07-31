@props(['product'])

<article class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white transition duration-300 hover:-translate-y-1.5 hover:border-primary-100 hover:shadow-2xl hover:shadow-primary-100/50">

    {{-- Badges --}}
    <div class="absolute left-3 top-3 z-10 flex flex-col gap-1.5">
        @if ($product->discount_percentage)
            <span class="rounded-full bg-gradient-to-r from-red-500 to-rose-500 px-2.5 py-1 text-[11px] font-bold text-white shadow-md shadow-red-500/30">-{{ $product->discount_percentage }}%</span>
        @endif
        @unless ($product->inStock())
            <span class="rounded-full bg-gray-900/80 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">Rupture</span>
        @endunless
    </div>

    {{-- Flèche au survol --}}
    <span class="absolute right-3 top-3 z-10 grid h-8 w-8 translate-y-1 place-items-center rounded-full bg-white/90 text-gray-700 opacity-0 shadow-md backdrop-blur transition duration-300 group-hover:translate-y-0 group-hover:opacity-100">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
    </span>

    {{-- Image --}}
    <a href="{{ route('shop.product', $product) }}" class="relative block aspect-square overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100" tabindex="-1">
        <img src="{{ $product->primaryImage?->url() ?? asset('images/placeholder-product.svg') }}"
             alt="{{ $product->primaryImage?->alt ?? $product->name }}"
             loading="lazy"
             class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-110">
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
            <span class="text-base font-bold text-gray-900 transition group-hover:text-primary-600">{{ format_price($product->current_price) }}</span>
            @if ($product->isOnSale())
                <span class="text-xs text-gray-400 line-through">{{ format_price($product->price) }}</span>
            @endif
        </div>
    </div>

    {{-- Liseré animé bas de carte --}}
    <span class="absolute inset-x-0 bottom-0 h-0.5 origin-left scale-x-0 bg-gradient-to-r from-primary-500 to-sky-400 transition-transform duration-300 group-hover:scale-x-100"></span>
</article>
