<x-shop-layout
    :title="$product->meta_title ?: $product->name"
    :metaDescription="$product->meta_description ?: $product->short_description"
    :whatsappMessage="'Je souhaite commander ce produit : '.$product->name"
    :ogImage="$product->primaryImage?->url()"
    ogType="product">

    <x-slot:head>
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'sku' => $product->sku,
            'description' => $product->short_description ?: strip_tags((string) $product->description),
            'image' => $product->images->map(fn ($img) => $img->url())->all() ?: [asset('images/placeholder-product.svg')],
            'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
            'category' => $product->category->name,
            'offers' => [
                '@type' => 'Offer',
                'url' => route('shop.product', $product),
                'price' => $product->current_price,
                'priceCurrency' => config('shop.currency'),
                'availability' => $product->inStock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            ],
            'aggregateRating' => $product->reviews_count > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => round((float) $product->rating, 1),
                'reviewCount' => $product->reviews_count,
            ] : null,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    </x-slot:head>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- Fil d'Ariane --}}
        <nav class="mb-6 text-xs text-gray-400" aria-label="Fil d'Ariane">
            <a href="{{ route('shop.home') }}" class="hover:text-primary-600">Accueil</a>
            <span class="mx-1.5">/</span>
            <a href="{{ route('shop.category', $product->category) }}" class="hover:text-primary-600">{{ $product->category->name }}</a>
            <span class="mx-1.5">/</span>
            <span class="text-gray-600">{{ $product->name }}</span>
        </nav>

        <div class="grid gap-10 lg:grid-cols-2">

            {{-- ========================= GALERIE ========================= --}}
            @php
                $gallery = $product->images->isNotEmpty()
                    ? $product->images->map(fn ($img) => ['url' => $img->url(), 'alt' => $img->alt ?? $product->name])->values()
                    : collect([['url' => asset('images/placeholder-product.svg'), 'alt' => $product->name]]);
            @endphp
            <div x-data="{ active: 0, zoom: false }" class="space-y-3">
                <div class="relative aspect-square overflow-hidden rounded-3xl border border-gray-100 bg-gray-50"
                     @mouseenter="zoom = true" @mouseleave="zoom = false">
                    @if ($product->discount_percentage)
                        <span class="absolute left-4 top-4 z-10 rounded-full bg-red-500 px-3 py-1 text-xs font-bold text-white">-{{ $product->discount_percentage }}%</span>
                    @endif
                    @foreach ($gallery as $index => $image)
                        <img x-show="active === {{ $index }}" src="{{ $image['url'] }}" alt="{{ $image['alt'] }}"
                             loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                             class="h-full w-full object-cover transition duration-500"
                             :class="zoom ? 'scale-125' : 'scale-100'">
                    @endforeach
                </div>

                @if ($gallery->count() > 1)
                    <div class="flex gap-3 overflow-x-auto pb-1">
                        @foreach ($gallery as $index => $image)
                            <button @click="active = {{ $index }}"
                                    class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border-2 transition"
                                    :class="active === {{ $index }} ? 'border-primary-600' : 'border-transparent opacity-60 hover:opacity-100'">
                                <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ====================== INFORMATIONS ======================= --}}
            <div>
                <div class="flex items-center gap-3 text-xs font-medium uppercase tracking-wide text-gray-400">
                    <span>{{ $product->category->name }}</span>
                    @if ($product->brand)
                        <span class="h-1 w-1 rounded-full bg-gray-300"></span>
                        <span class="text-primary-600">{{ $product->brand->name }}</span>
                    @endif
                </div>

                <h1 class="mt-2 text-2xl font-extrabold leading-tight tracking-tight sm:text-3xl">{{ $product->name }}</h1>

                <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-gray-500">
                    @if ($product->reviews_count > 0)
                        <span class="flex items-center gap-1.5">
                            <x-rating-stars :rating="$product->rating" />
                            ({{ $product->reviews_count }} avis)
                        </span>
                        <span class="h-1 w-1 rounded-full bg-gray-300"></span>
                    @endif
                    <span>Réf. : <span class="font-medium text-gray-700">{{ $product->sku }}</span></span>
                </div>

                {{-- Prix --}}
                <div class="mt-5 flex items-baseline gap-3">
                    <span class="text-3xl font-extrabold text-gray-900">{{ format_price($product->current_price) }}</span>
                    @if ($product->isOnSale())
                        <span class="text-lg text-gray-400 line-through">{{ format_price($product->price) }}</span>
                        <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-600">Économisez {{ format_price($product->price - $product->current_price) }}</span>
                    @endif
                </div>

                {{-- Stock --}}
                <div class="mt-4">
                    @if (! $product->inStock())
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Rupture de stock
                        </span>
                    @elseif ($product->isLowStock())
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Plus que {{ $product->stock_quantity }} en stock !
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> En stock
                        </span>
                    @endif
                </div>

                @if ($product->short_description)
                    <p class="mt-5 text-sm leading-relaxed text-gray-600">{{ $product->short_description }}</p>
                @endif

                {{-- Actions --}}
                <div class="mt-7 space-y-3" x-data="{ qty: 1, max: {{ $product->stock_quantity }} }">
                    @if ($product->inStock())
                        <div class="flex items-center gap-4">
                            <div class="flex items-center rounded-full border border-gray-200">
                                <button type="button" @click="qty = Math.max(1, qty - 1)" aria-label="Diminuer"
                                        class="grid h-11 w-11 place-items-center text-gray-500 transition hover:text-primary-600">−</button>
                                <span class="w-8 text-center text-sm font-semibold" x-text="qty"></span>
                                <button type="button" @click="qty = Math.min(max, qty + 1)" aria-label="Augmenter"
                                        class="grid h-11 w-11 place-items-center text-gray-500 transition hover:text-primary-600">+</button>
                            </div>
                            <p class="text-xs text-gray-400">Quantité</p>
                        </div>

                        @if (Route::has('shop.cart.add'))
                            <form action="{{ route('shop.cart.add', $product) }}" method="POST" class="flex flex-col gap-3 sm:flex-row">
                                @csrf
                                <input type="hidden" name="quantity" :value="qty">
                                <button type="submit"
                                        class="flex-1 rounded-full bg-primary-600 py-3.5 text-sm font-semibold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700">
                                    Ajouter au panier
                                </button>
                                <button type="submit" name="buy_now" value="1"
                                        class="flex-1 rounded-full bg-gray-950 py-3.5 text-sm font-semibold text-white transition hover:bg-gray-800">
                                    Acheter maintenant
                                </button>
                            </form>
                        @endif
                    @endif

                    <a href="{{ whatsapp_link('Je souhaite commander ce produit : '.$product->name) }}" target="_blank" rel="noopener"
                       class="flex w-full items-center justify-center gap-2 rounded-full border-2 border-emerald-500 py-3 text-sm font-semibold text-emerald-600 transition hover:bg-emerald-500 hover:text-white">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                        Commander sur WhatsApp
                    </a>
                </div>

                {{-- Réassurance --}}
                <ul class="mt-7 grid grid-cols-3 gap-3 border-t border-gray-100 pt-6 text-center text-xs text-gray-500">
                    <li>
                        <svg class="mx-auto mb-1.5 h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.9 17.9 0 0 0-.437-3.109m-14.978 0h14.978m-14.978 0-.964-6.023A1.125 1.125 0 0 1 6.27 6.75h11.46c.554 0 1.026.4 1.11.947l.964 6.023"/></svg>
                        Livraison 24-72h
                    </li>
                    <li>
                        <svg class="mx-auto mb-1.5 h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        Produit authentique
                    </li>
                    <li>
                        <svg class="mx-auto mb-1.5 h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                        Paiement sécurisé
                    </li>
                </ul>
            </div>
        </div>

        {{-- ============== DESCRIPTION / CARACTÉRISTIQUES / AVIS ========== --}}
        <div class="mt-14" x-data="{ tab: 'description' }">
            <div class="flex gap-1 overflow-x-auto border-b border-gray-100">
                <button @click="tab = 'description'"
                        class="whitespace-nowrap border-b-2 px-5 py-3 text-sm font-semibold transition"
                        :class="tab === 'description' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-900'">
                    Description
                </button>
                @if ($product->features)
                    <button @click="tab = 'features'"
                            class="whitespace-nowrap border-b-2 px-5 py-3 text-sm font-semibold transition"
                            :class="tab === 'features' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-900'">
                        Caractéristiques
                    </button>
                @endif
                <button @click="tab = 'reviews'"
                        class="whitespace-nowrap border-b-2 px-5 py-3 text-sm font-semibold transition"
                        :class="tab === 'reviews' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-900'">
                    Avis ({{ $product->reviews_count }})
                </button>
            </div>

            <div class="py-8">
                <div x-show="tab === 'description'" class="max-w-3xl text-sm leading-relaxed text-gray-600">
                    {!! nl2br(e($product->description)) !!}
                </div>

                @if ($product->features)
                    <div x-show="tab === 'features'" x-cloak class="max-w-2xl overflow-hidden rounded-2xl border border-gray-100">
                        <table class="w-full text-sm">
                            <tbody>
                                @foreach ($product->features as $label => $value)
                                    <tr class="border-b border-gray-100 last:border-0 odd:bg-gray-50/60">
                                        <th class="w-1/3 px-5 py-3 text-left font-semibold text-gray-900">{{ $label }}</th>
                                        <td class="px-5 py-3 text-gray-600">{{ $value }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div x-show="tab === 'reviews'" x-cloak class="max-w-3xl space-y-5">
                    @forelse ($product->approvedReviews as $review)
                        <article class="rounded-2xl border border-gray-100 p-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-full bg-primary-600 text-sm font-bold text-white">
                                        {{ strtoupper(mb_substr($review->author_name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold">{{ $review->author_name }}</p>
                                        <p class="text-xs text-gray-400">{{ $review->created_at->translatedFormat('d F Y') }}</p>
                                    </div>
                                </div>
                                <x-rating-stars :rating="$review->rating" />
                            </div>
                            @if ($review->comment)
                                <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ $review->comment }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">Aucun avis pour ce produit pour le moment.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ===================== PRODUITS SIMILAIRES ===================== --}}
        @if ($similar->isNotEmpty())
            <section class="mt-8 border-t border-gray-100 pt-12">
                <h2 class="mb-8 text-xl font-extrabold tracking-tight sm:text-2xl">Produits similaires</h2>
                <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                    @foreach ($similar as $item)
                        <x-product-card :product="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

</x-shop-layout>
