{{--
    Fragment injecté dans la modale Quick View (voir composant global
    dans components/shop-layout.blade.php). Pas de <html>/layout ici :
    uniquement le contenu, chargé en fetch() par Alpine.
--}}
@php
    $gallery = $product->images->isNotEmpty()
        ? $product->images->map(fn ($img) => $img->url())->values()
        : collect([asset('images/placeholder-product.svg')]);
    $inWishlist = app(\App\Services\Wishlist\WishlistService::class)->has($product->id);
@endphp

<div class="grid gap-6 sm:grid-cols-2" x-data="{ active: 0 }">
    {{-- Galerie --}}
    <div>
        <div class="aspect-square overflow-hidden rounded-2xl bg-gray-50">
            @foreach ($gallery as $index => $url)
                <img x-show="active === {{ $index }}" x-cloak src="{{ $url }}" alt="{{ $product->name }}"
                     class="h-full w-full object-cover">
            @endforeach
        </div>
        @if ($gallery->count() > 1)
            <div class="mt-3 flex gap-2 overflow-x-auto">
                @foreach ($gallery as $index => $url)
                    <button type="button" @click="active = {{ $index }}"
                            class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border-2 transition"
                            :class="active === {{ $index }} ? 'border-primary-600' : 'border-transparent opacity-60 hover:opacity-100'">
                        <img src="{{ $url }}" alt="" class="h-full w-full object-cover">
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Informations --}}
    <div class="flex flex-col">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $product->category->name }}</p>
        <h2 class="mt-1 font-display text-xl font-bold leading-snug text-gray-900">{{ $product->name }}</h2>

        @if ($product->reviews_count > 0)
            <div class="mt-2 flex items-center gap-1.5 text-sm text-gray-500">
                <x-rating-stars :rating="$product->rating" size="h-3.5 w-3.5" />
                <span>({{ $product->reviews_count }})</span>
            </div>
        @endif

        <div class="mt-3 flex items-baseline gap-2">
            <span class="text-2xl font-extrabold text-gray-900">{{ format_price($product->current_price) }}</span>
            @if ($product->isOnSale())
                <span class="text-sm text-gray-400 line-through">{{ format_price($product->price) }}</span>
                <span class="rounded-full bg-danger-50 px-2 py-0.5 text-xs font-bold text-danger-600">-{{ $product->discount_percentage }}%</span>
            @endif
        </div>

        @if ($product->short_description)
            <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-gray-500">{{ $product->short_description }}</p>
        @endif

        <div class="mt-4">
            @if (! $product->inStock())
                <span class="inline-flex items-center gap-1.5 rounded-full bg-danger-50 px-3 py-1 text-xs font-semibold text-danger-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-danger-500"></span> Rupture de stock
                </span>
            @elseif ($product->isLowStock())
                <span class="inline-flex items-center gap-1.5 rounded-full bg-warning-50 px-3 py-1 text-xs font-semibold text-warning-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-warning-500"></span> Plus que {{ $product->stock_quantity }} en stock
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-3 py-1 text-xs font-semibold text-success-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span> En stock
                </span>
            @endif
        </div>

        <div class="mt-6 flex flex-col gap-2.5 sm:flex-row">
            @if ($product->inStock())
                <form action="{{ route('shop.cart.add', $product) }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="quick" value="1">
                    <x-btn class="w-full">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                        Ajouter au panier
                    </x-btn>
                </form>
            @endif
            <form action="{{ route('shop.wishlist.toggle', $product) }}" method="POST">
                @csrf
                <button type="submit" aria-label="{{ $inWishlist ? 'Retirer des favoris' : 'Ajouter aux favoris' }}"
                        class="grid h-12 w-12 place-items-center rounded-full border-2 transition {{ $inWishlist ? 'border-danger-200 bg-danger-50 text-danger-500' : 'border-gray-200 text-gray-400 hover:border-danger-200 hover:text-danger-500' }}">
                    <svg class="h-5 w-5" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
                    </svg>
                </button>
            </form>
        </div>

        <a href="{{ route('shop.product', $product) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-700">
            Voir la fiche complète
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
        </a>
    </div>
</div>
