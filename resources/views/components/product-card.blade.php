@props(['product'])

@php $inWishlist = app(\App\Services\Wishlist\WishlistService::class)->has($product->id); @endphp

<article class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white transition duration-300 hover:-translate-y-1.5 hover:border-primary-100 hover:shadow-2xl hover:shadow-primary-100/50">

    {{-- Badges --}}
    <div class="absolute left-3 top-3 z-10 flex flex-col items-start gap-1.5">
        @if ($product->discount_percentage)
            <span class="rounded-full bg-gradient-to-r from-danger-500 to-rose-500 px-2.5 py-1 text-[11px] font-bold text-white shadow-md shadow-danger-500/30">-{{ $product->discount_percentage }}%</span>
        @elseif ($product->isNew())
            <span class="rounded-full bg-gradient-to-r from-success-500 to-teal-500 px-2.5 py-1 text-[11px] font-bold text-white shadow-md shadow-success-500/30">Nouveau</span>
        @endif
        @unless ($product->inStock())
            <span class="rounded-full bg-gray-900/80 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">Rupture</span>
        @endunless
    </div>

    {{-- Favoris --}}
    <form action="{{ route('shop.wishlist.toggle', $product) }}" method="POST" class="absolute right-3 top-3 z-10">
        @csrf
        <button type="submit"
                aria-label="{{ $inWishlist ? 'Retirer des favoris' : 'Ajouter aux favoris' }}"
                aria-pressed="{{ $inWishlist ? 'true' : 'false' }}"
                class="grid h-9 w-9 place-items-center rounded-full bg-white/90 shadow-md backdrop-blur transition duration-300 hover:scale-110 {{ $inWishlist ? 'text-danger-500' : 'text-gray-400 hover:text-danger-500' }}">
            <svg class="h-5 w-5" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
            </svg>
        </button>
    </form>

    {{-- Image (placeholder shimmer pendant le chargement) --}}
    <a href="{{ route('shop.product', $product) }}" class="img-skeleton relative block aspect-square overflow-hidden" tabindex="-1">
        <img src="{{ $product->primaryImage?->url() ?? asset('images/placeholder-product.svg') }}"
             alt="{{ $product->primaryImage?->alt ?? $product->name }}"
             loading="lazy"
             class="relative h-full w-full bg-gray-50 object-cover transition duration-700 ease-out group-hover:scale-110">
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

        <div class="mt-auto flex items-center justify-between gap-2 pt-1.5">
            <span class="flex min-w-0 items-baseline gap-2">
                <span class="text-base font-bold text-gray-900 transition group-hover:text-primary-600">{{ format_price($product->current_price) }}</span>
                @if ($product->isOnSale())
                    <span class="text-xs text-gray-400 line-through">{{ format_price($product->price) }}</span>
                @endif
            </span>

            {{-- Ajout au panier en un clic --}}
            @if ($product->inStock())
                <form action="{{ route('shop.cart.add', $product) }}" method="POST" class="relative z-10 shrink-0">
                    @csrf
                    <input type="hidden" name="quick" value="1">
                    <button type="submit" aria-label="Ajouter au panier"
                            class="grid h-9 w-9 place-items-center rounded-full bg-gray-100 text-gray-600 transition duration-300 hover:scale-110 hover:bg-gradient-to-br hover:from-primary-500 hover:to-primary-700 hover:text-white hover:shadow-lg hover:shadow-primary-500/40">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Liseré animé bas de carte --}}
    <span class="absolute inset-x-0 bottom-0 h-0.5 origin-left scale-x-0 bg-gradient-to-r from-primary-500 to-accent-400 transition-transform duration-300 group-hover:scale-x-100"></span>
</article>
