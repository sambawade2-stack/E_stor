<x-shop-layout :title="$title" :metaDescription="'Découvrez '.$title.' — '.setting('shop_name').' : accessoires électroniques authentiques au meilleur prix.'">

    {{-- Fil d'Ariane + titre --}}
    <div class="border-b border-gray-100 bg-gray-50/70">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <nav class="mb-2 text-xs text-gray-400" aria-label="Fil d'Ariane">
                <a href="{{ route('shop.home') }}" class="hover:text-primary-600">Accueil</a>
                <span class="mx-1.5">/</span>
                @if ($category)
                    <a href="{{ route('shop.catalog') }}" class="hover:text-primary-600">Catalogue</a>
                    <span class="mx-1.5">/</span>
                @endif
                <span class="text-gray-600">{{ $title }}</span>
            </nav>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $title }}</h1>
            @if ($category?->description)
                <p class="mt-1.5 max-w-2xl text-sm text-gray-500">{{ $category->description }}</p>
            @endif
            @if (request('q'))
                <p class="mt-1.5 text-sm text-gray-500">Résultats pour « <strong>{{ request('q') }}</strong> »</p>
            @endif
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[16rem_1fr]">

            {{-- ========================= FILTRES ========================= --}}
            <aside x-data="{ open: false }">
                <button @click="open = !open"
                        class="mb-4 flex w-full items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold lg:hidden">
                    Filtres
                    <svg class="h-4 w-4 transition" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>

                <form method="GET" :class="open ? 'block' : 'hidden lg:block'" class="space-y-6">
                    @if (request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    @if (request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif

                    {{-- Catégories --}}
                    @unless ($category)
                        <fieldset>
                            <legend class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-900">Catégories</legend>
                            <ul class="space-y-1.5 text-sm">
                                @foreach ($categories as $cat)
                                    <li>
                                        <a href="{{ route('shop.category', $cat) }}" class="text-gray-600 transition hover:text-primary-600">{{ $cat->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </fieldset>
                    @endunless

                    {{-- Marque --}}
                    <fieldset>
                        <legend class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-900">Marque</legend>
                        <select name="brand" class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Toutes les marques</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->slug }}" @selected(request('brand') === $brand->slug)>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </fieldset>

                    {{-- Prix --}}
                    <fieldset>
                        <legend class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-900">Prix ({{ setting('currency_symbol') }})</legend>
                        <div class="flex items-center gap-2">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" min="0" placeholder="Min"
                                   class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <span class="text-gray-300">—</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" min="0" placeholder="Max"
                                   class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </fieldset>

                    {{-- Disponibilité --}}
                    <label class="flex items-center gap-2.5 text-sm text-gray-700">
                        <input type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock'))
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        En stock uniquement
                    </label>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 rounded-full bg-primary-600 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700">Filtrer</button>
                        <a href="{{ $category ? route('shop.category', $category) : ($onSaleOnly ? route('shop.promotions') : route('shop.catalog')) }}"
                           class="rounded-full border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-500 transition hover:bg-gray-50">Réinitialiser</a>
                    </div>
                </form>
            </aside>

            {{-- ========================= RÉSULTATS ======================= --}}
            <div>
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-gray-500">
                        <strong class="font-semibold text-gray-900">{{ $products->total() }}</strong> produit{{ $products->total() > 1 ? 's' : '' }}
                    </p>

                    <form method="GET" class="flex items-center gap-2">
                        @foreach (request()->except(['sort', 'page']) as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <label for="sort" class="text-sm text-gray-500">Trier :</label>
                        <select id="sort" name="sort" onchange="this.form.submit()"
                                class="rounded-xl border-gray-200 py-2 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="recent" @selected(request('sort', 'recent') === 'recent')>Nouveautés</option>
                            <option value="popular" @selected(request('sort') === 'popular')>Popularité</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>Prix croissant</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>Prix décroissant</option>
                        </select>
                    </form>
                </div>

                @if ($products->isEmpty())
                    <div class="rounded-2xl border border-dashed border-gray-200 py-20 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <p class="mt-4 font-semibold text-gray-900">Aucun produit trouvé</p>
                        <p class="mt-1 text-sm text-gray-500">Essayez de modifier vos filtres ou votre recherche.</p>
                        <a href="{{ route('shop.catalog') }}" class="mt-5 inline-block rounded-full bg-primary-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700">
                            Voir tout le catalogue
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-4 sm:gap-6 xl:grid-cols-3">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    <div class="mt-10">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-shop-layout>
