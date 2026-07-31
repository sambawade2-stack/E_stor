<x-shop-layout>

    {{-- ============================= HERO ============================= --}}
    <section class="relative overflow-hidden bg-gray-950 text-white">
        <div class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 rounded-full bg-primary-600/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-40 right-0 h-96 w-96 rounded-full bg-primary-500/20 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:py-24">
            <div>
                <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 text-xs font-medium text-gray-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    Livraison rapide partout au Sénégal
                </p>
                <h1 class="text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                    La technologie<br>
                    <span class="bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">à portée de main</span>
                </h1>
                <p class="mt-5 max-w-lg text-base leading-relaxed text-gray-400 sm:text-lg">
                    Écouteurs Bluetooth, chargeurs, power banks, répéteurs WiFi et accessoires authentiques au meilleur prix.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('shop.catalog') }}"
                       class="rounded-full bg-primary-600 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-primary-600/30 transition hover:bg-primary-500">
                        Découvrir le catalogue
                    </a>
                    <a href="{{ route('shop.promotions') }}"
                       class="rounded-full border border-white/15 bg-white/5 px-7 py-3.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/10">
                        Voir les promotions
                    </a>
                </div>

                <dl class="mt-10 grid max-w-md grid-cols-3 gap-4 border-t border-white/10 pt-6 text-center sm:text-left">
                    <div><dt class="text-2xl font-extrabold">100%</dt><dd class="text-xs text-gray-400">Produits authentiques</dd></div>
                    <div><dt class="text-2xl font-extrabold">24-72h</dt><dd class="text-xs text-gray-400">Livraison au Sénégal</dd></div>
                    <div><dt class="text-2xl font-extrabold">7j/7</dt><dd class="text-xs text-gray-400">Support WhatsApp</dd></div>
                </dl>
            </div>

            {{-- Visuel hero : produit vedette --}}
            @if ($featured->isNotEmpty())
                @php $hero = $featured->first(); @endphp
                <div class="relative hidden lg:block">
                    <div class="mx-auto max-w-md rounded-3xl border border-white/10 bg-white/5 p-8 backdrop-blur">
                        <img src="{{ $hero->primaryImage?->url() ?? asset('images/placeholder-product.svg') }}"
                             alt="{{ $hero->name }}" class="aspect-square w-full rounded-2xl object-cover" loading="eager">
                        <div class="mt-5 flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-400">{{ $hero->category->name }}</p>
                                <p class="mt-0.5 line-clamp-1 font-semibold">{{ $hero->name }}</p>
                            </div>
                            <p class="shrink-0 text-lg font-extrabold text-primary-400">{{ format_price($hero->current_price) }}</p>
                        </div>
                        <a href="{{ route('shop.product', $hero) }}"
                           class="mt-4 block rounded-full bg-white py-2.5 text-center text-sm font-semibold text-gray-900 transition hover:bg-gray-100">
                            Voir le produit
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- ========================= CATÉGORIES =========================== --}}
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-end justify-between">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Nos catégories</h2>
                <p class="mt-1 text-sm text-gray-500">Trouvez exactement ce qu'il vous faut</p>
            </div>
        </div>

        @php
            $categoryIcons = [
                'accessoires-telephoniques' => 'M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3',
                'ecouteurs-bluetooth' => 'M3 18.75V16.5a9 9 0 0 1 18 0v2.25m-18 0a2.25 2.25 0 0 0 2.25 2.25h1.5a1.5 1.5 0 0 0 1.5-1.5v-3a1.5 1.5 0 0 0-1.5-1.5h-1.5A2.25 2.25 0 0 0 3 17.25v1.5Zm18 0a2.25 2.25 0 0 1-2.25 2.25h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5A2.25 2.25 0 0 1 21 17.25v1.5Z',
                'chargeurs' => 'M3.75 13.5 14.25 2.25 12 10.5h8.25L9.75 21.75 12 13.5H3.75Z',
                'power-banks' => 'M21 10.5h.375c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125H21M4.5 10.5H18V15H4.5v-4.5ZM3.75 18h15A2.25 2.25 0 0 0 21 15.75v-6a2.25 2.25 0 0 0-2.25-2.25h-15A2.25 2.25 0 0 0 1.5 9.75v6A2.25 2.25 0 0 0 3.75 18Z',
                'repeteurs-wifi' => 'M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z',
                'autres-accessoires' => 'M21 7.5V18M15 7.5V18M3 16.811V8.69c0-.864.933-1.406 1.683-.977l7.108 4.061a1.125 1.125 0 0 1 0 1.954l-7.108 4.061A1.125 1.125 0 0 1 3 16.811Z',
            ];
            $fallbackIcon = 'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9';
        @endphp

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($categories as $category)
                <a href="{{ route('shop.category', $category) }}"
                   class="group flex flex-col items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50/60 p-6 text-center transition hover:-translate-y-1 hover:border-primary-200 hover:bg-white hover:shadow-lg hover:shadow-gray-200/60">
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-white text-gray-500 shadow-sm transition group-hover:bg-primary-600 group-hover:text-white">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $categoryIcons[$category->slug] ?? $fallbackIcon }}"/>
                        </svg>
                    </span>
                    <span>
                        <span class="block text-sm font-semibold text-gray-900">{{ $category->name }}</span>
                        <span class="mt-0.5 block text-xs text-gray-400">{{ $category->products_count }} produit{{ $category->products_count > 1 ? 's' : '' }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ====================== PRODUITS VEDETTES ======================= --}}
    @if ($featured->isNotEmpty())
        <section class="bg-gray-50/70 py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-end justify-between">
                    <div>
                        <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Produits vedettes</h2>
                        <p class="mt-1 text-sm text-gray-500">Notre sélection du moment</p>
                    </div>
                    <a href="{{ route('shop.catalog') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700">Tout voir →</a>
                </div>
                <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                    @foreach ($featured->take(4) as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================= PROMOTIONS =========================== --}}
    @if ($onSale->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl bg-gray-950 p-6 sm:p-10">
                <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="mb-1 inline-block rounded-full bg-red-500 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white">Offres limitées</p>
                        <h2 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">En promotion</h2>
                    </div>
                    <a href="{{ route('shop.promotions') }}" class="text-sm font-semibold text-primary-400 hover:text-primary-300">Toutes les promos →</a>
                </div>
                <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                    @foreach ($onSale as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================= NOUVEAUTÉS =========================== --}}
    <section class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-end justify-between">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Nouveautés</h2>
                <p class="mt-1 text-sm text-gray-500">Les derniers arrivages</p>
            </div>
            <a href="{{ route('shop.catalog', ['sort' => 'recent']) }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700">Tout voir →</a>
        </div>
        <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
            @foreach ($latest->take(8) as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </section>

    {{-- ===================== PRODUITS POPULAIRES ====================== --}}
    @if ($popular->isNotEmpty())
        <section class="bg-gray-50/70 py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-end justify-between">
                    <div>
                        <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Les plus populaires</h2>
                        <p class="mt-1 text-sm text-gray-500">Ce que nos clients préfèrent</p>
                    </div>
                    <a href="{{ route('shop.catalog', ['sort' => 'popular']) }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700">Tout voir →</a>
                </div>
                <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                    @foreach ($popular->take(4) as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ====================== POURQUOI NOUS CHOISIR =================== --}}
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <h2 class="mb-10 text-center text-2xl font-extrabold tracking-tight sm:text-3xl">Pourquoi nous choisir ?</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['title' => 'Livraison rapide', 'text' => 'Livraison en 24h à Dakar et 48-72h dans les régions.', 'icon' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.9 17.9 0 0 0-.437-3.109m-14.978 0h14.978m-14.978 0-.964-6.023A1.125 1.125 0 0 1 6.27 6.75h11.46c.554 0 1.026.4 1.11.947l.964 6.023'],
                ['title' => 'Produits authentiques', 'text' => 'Uniquement des produits originaux des plus grandes marques.', 'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
                ['title' => 'Paiement sécurisé', 'text' => 'Wave, Orange Money, carte bancaire ou paiement à la livraison.', 'icon' => 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z'],
                ['title' => 'Support 7j/7', 'text' => 'Une question ? Notre équipe vous répond sur WhatsApp.', 'icon' => 'M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z'],
            ] as $feature)
                <div class="rounded-2xl border border-gray-100 p-6 text-center transition hover:border-primary-100 hover:shadow-lg hover:shadow-gray-200/60">
                    <span class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-xl bg-primary-50 text-primary-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/></svg>
                    </span>
                    <h3 class="font-semibold">{{ $feature['title'] }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-gray-500">{{ $feature['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ======================== AVIS CLIENTS ========================== --}}
    @if ($reviews->isNotEmpty())
        <section class="bg-gray-50/70 py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="mb-10 text-center text-2xl font-extrabold tracking-tight sm:text-3xl">Ils nous font confiance</h2>
                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($reviews as $review)
                        <figure class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                            <x-rating-stars :rating="$review->rating" />
                            <blockquote class="mt-3 text-sm leading-relaxed text-gray-600">« {{ $review->comment }} »</blockquote>
                            <figcaption class="mt-4 flex items-center gap-3">
                                <span class="grid h-10 w-10 place-items-center rounded-full bg-primary-600 text-sm font-bold text-white">
                                    {{ strtoupper(mb_substr($review->author_name, 0, 1)) }}
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold">{{ $review->author_name }}</span>
                                    <span class="block text-xs text-gray-400">{{ $review->product->name }}</span>
                                </span>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================= NEWSLETTER =========================== --}}
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-primary-600 px-6 py-12 text-center text-white sm:px-12">
            <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -left-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>

            <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Restez informé(e)</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm text-primary-100">
                Recevez en avant-première nos nouveautés et promotions exclusives.
            </p>

            @if (session('newsletter_success'))
                <p class="mx-auto mt-6 w-fit rounded-full bg-white/15 px-5 py-2.5 text-sm font-medium">✓ {{ session('newsletter_success') }}</p>
            @else
                <form action="{{ route('shop.newsletter.store') }}" method="POST" class="mx-auto mt-6 flex max-w-md flex-col gap-3 sm:flex-row">
                    @csrf
                    <input type="email" name="email" required placeholder="Votre adresse email"
                           class="w-full rounded-full border-0 px-5 py-3 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-white">
                    <button type="submit"
                            class="shrink-0 rounded-full bg-gray-950 px-7 py-3 text-sm font-semibold text-white transition hover:bg-gray-800">
                        S'inscrire
                    </button>
                </form>
                @error('email')
                    <p class="mt-2 text-sm text-primary-100">{{ $message }}</p>
                @enderror
            @endif
        </div>
    </section>

    {{-- ====================== MARQUES PARTENAIRES ===================== --}}
    @if ($brands->isNotEmpty())
        <section class="border-t border-gray-100 py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="mb-6 text-center text-xs font-semibold uppercase tracking-widest text-gray-400">Nos marques partenaires</p>
                <div class="flex flex-wrap items-center justify-center gap-x-10 gap-y-4">
                    @foreach ($brands as $brand)
                        @if ($brand->logo)
                            <img src="{{ Storage::url($brand->logo) }}" alt="{{ $brand->name }}" loading="lazy" class="h-8 w-auto opacity-60 grayscale transition hover:opacity-100 hover:grayscale-0">
                        @else
                            <span class="text-lg font-bold text-gray-300 transition hover:text-gray-500">{{ $brand->name }}</span>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</x-shop-layout>
