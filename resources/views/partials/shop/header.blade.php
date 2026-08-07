<header class="sticky top-0 z-40 border-b border-gray-100/80 bg-white/85 backdrop-blur-xl transition-shadow duration-300"
        x-data="{ mobileOpen: false, catOpen: false, scrolled: false, bumpCart: false, bumpWishlist: false }"
        @scroll.window.passive="scrolled = window.scrollY > 8"
        @bump-cart.window="bumpCart = true; setTimeout(() => bumpCart = false, 700)"
        @bump-wishlist.window="bumpWishlist = true; setTimeout(() => bumpWishlist = false, 700)"
        :class="scrolled && 'shadow-lg shadow-gray-900/5'">

    {{-- Barre supérieure --}}
    <div class="hidden bg-gray-900 text-xs text-gray-300 sm:block">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-1.5 sm:px-6 lg:px-8">
            <p>
                <svg class="mr-1 inline h-3.5 w-3.5 text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.9 17.9 0 0 0-.437-3.109m-14.978 0h14.978m-14.978 0-.964-6.023A1.125 1.125 0 0 1 6.27 6.75h11.46c.554 0 1.026.4 1.11.947l.964 6.023"/></svg>
                Livraison rapide à Dakar et partout au Sénégal
            </p>
            <div class="flex items-center gap-4">
                <a href="tel:{{ preg_replace('/\s+/', '', setting('shop_phone')) }}" class="hover:text-white">{{ setting('shop_phone') }}</a>
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="font-semibold text-primary-400 hover:text-primary-300">Administration</a>
                    @endif
                    <a href="{{ route('dashboard') }}" class="hover:text-white">Mon compte</a>
                @else
                    {{-- Achat sans compte : on propose le suivi de commande
                         plutôt qu'une connexion ou une inscription. --}}
                    <a href="{{ route('shop.order.track') }}" class="hover:text-white">Suivre ma commande</a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Barre principale --}}
    <div class="mx-auto flex max-w-7xl items-center gap-4 px-4 py-3 sm:px-6 lg:gap-8 lg:px-8">

        {{-- Logo --}}
        <a href="{{ route('shop.home') }}" class="flex shrink-0 items-center gap-2.5">
            @if(setting('logo_path'))
                <img src="{{ Storage::disk('public')->url(setting('logo_path')) }}" alt="{{ setting('shop_name') }}" class="h-10 w-auto">
            @else
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-gray-900 text-lg font-extrabold leading-none text-white">
                    <span class="whitespace-nowrap">E<span class="text-primary-500">S</span></span>
                </span>
                <span class="hidden flex-col leading-tight md:flex">
                    <span class="text-base font-extrabold tracking-tight text-gray-900">Électroniques <span class="text-primary-600">Stores</span></span>
                    <span class="text-[10px] font-medium uppercase tracking-widest text-gray-400">{{ setting('shop_tagline') }}</span>
                </span>
            @endif
        </a>

        {{-- Recherche --}}
        <form action="{{ route('shop.catalog') }}" method="GET" class="min-w-0 flex-1">
            <div class="relative">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher un produit, une marque…"
                       class="w-full rounded-full border-gray-200 bg-gray-50 py-2.5 pl-4 pr-11 text-sm placeholder-gray-400 focus:border-primary-500 focus:bg-white focus:ring-primary-500">
                <button type="submit" aria-label="Rechercher"
                        class="absolute inset-y-0 right-0 my-1 mr-1 grid w-9 place-items-center rounded-full bg-primary-600 text-white transition hover:bg-primary-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </button>
            </div>
        </form>

        {{-- Actions --}}
        <div class="flex shrink-0 items-center gap-1 sm:gap-2">
            {{-- Pour un visiteur, l'icône mène au suivi de commande : pas de
                 compte requis pour acheter ni pour suivre sa livraison. --}}
            @auth
                <a href="{{ route('dashboard') }}" aria-label="Mon compte"
                   class="grid h-10 w-10 place-items-center rounded-full text-gray-600 transition hover:bg-gray-100 hover:text-primary-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </a>
            @else
                <a href="{{ route('shop.order.track') }}" aria-label="Suivre ma commande"
                   class="grid h-10 w-10 place-items-center rounded-full text-gray-600 transition hover:bg-gray-100 hover:text-primary-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-6"/></svg>
                </a>
            @endauth

            <a href="{{ route('shop.wishlist') }}" aria-label="Mes favoris"
               class="relative grid h-10 w-10 place-items-center rounded-full text-gray-600 transition hover:bg-gray-100 hover:text-danger-500"
               :class="bumpWishlist && 'animate-bump'">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                @if(($wishlistCount ?? 0) > 0)
                    <span class="absolute -right-0.5 -top-0.5 grid h-5 min-w-5 place-items-center rounded-full bg-gradient-to-br from-danger-400 to-danger-600 px-1 text-[10px] font-bold text-white shadow-md shadow-danger-500/40">{{ $wishlistCount }}</span>
                @endif
            </a>

            @if (Route::has('shop.cart'))
                <a href="{{ route('shop.cart') }}" aria-label="Panier"
                   class="relative grid h-10 w-10 place-items-center rounded-full text-gray-600 transition hover:bg-gray-100 hover:text-primary-600"
                   :class="bumpCart && 'animate-bump'">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                    @if(($cartCount ?? 0) > 0)
                        <span class="absolute -right-0.5 -top-0.5 grid h-5 min-w-5 place-items-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 px-1 text-[10px] font-bold text-white shadow-md shadow-primary-600/40">{{ $cartCount }}</span>
                    @endif
                </a>
            @endif

            {{-- Burger mobile --}}
            <button @click="mobileOpen = !mobileOpen" aria-label="Menu"
                    class="grid h-10 w-10 place-items-center rounded-full text-gray-600 transition hover:bg-gray-100 lg:hidden">
                <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                <svg x-show="mobileOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- Navigation desktop --}}
    <nav class="hidden border-t border-gray-100 lg:block">
        <div class="mx-auto flex max-w-7xl items-center gap-1 px-4 sm:px-6 lg:px-8">

            <div class="relative" @mouseenter="catOpen = true" @mouseleave="catOpen = false">
                <button class="flex items-center gap-2 rounded-t-lg px-4 py-3 text-sm font-semibold text-gray-900 hover:text-primary-600"
                        :class="catOpen && 'text-primary-600'">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    Toutes les catégories
                    <svg class="h-3.5 w-3.5 transition" :class="catOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div x-show="catOpen" x-cloak x-transition.opacity.duration.150ms
                     class="absolute left-0 top-full z-50 w-64 rounded-b-xl border border-gray-100 bg-white py-2 shadow-xl">
                    @foreach ($navCategories as $cat)
                        <a href="{{ route('shop.category', $cat) }}"
                           class="block px-4 py-2.5 text-sm text-gray-700 transition hover:bg-primary-50 hover:text-primary-700">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('shop.home') }}" class="px-4 py-3 text-sm font-medium {{ request()->routeIs('shop.home') ? 'text-primary-600' : 'text-gray-600 hover:text-primary-600' }}">Accueil</a>
            <a href="{{ route('shop.catalog') }}" class="px-4 py-3 text-sm font-medium {{ request()->routeIs('shop.catalog') ? 'text-primary-600' : 'text-gray-600 hover:text-primary-600' }}">Catalogue</a>
            <a href="{{ route('shop.promotions') }}" class="flex items-center gap-1.5 px-4 py-3 text-sm font-medium {{ request()->routeIs('shop.promotions') ? 'text-primary-600' : 'text-gray-600 hover:text-primary-600' }}">
                Promotions
                <span class="rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-red-600">Promo</span>
            </a>
            <a href="{{ route('shop.contact') }}" class="px-4 py-3 text-sm font-medium {{ request()->routeIs('shop.contact') ? 'text-primary-600' : 'text-gray-600 hover:text-primary-600' }}">Contact</a>
            <a href="{{ route('shop.about') }}" class="px-4 py-3 text-sm font-medium {{ request()->routeIs('shop.about') ? 'text-primary-600' : 'text-gray-600 hover:text-primary-600' }}">À propos</a>
        </div>
    </nav>

    {{-- Menu mobile --}}
    <div x-show="mobileOpen" x-cloak x-transition.opacity.duration.150ms
         class="border-t border-gray-100 bg-white lg:hidden">
        <nav class="space-y-1 px-4 py-3">
            <a href="{{ route('shop.home') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Accueil</a>
            <a href="{{ route('shop.catalog') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Catalogue</a>
            <a href="{{ route('shop.promotions') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50">🔥 Promotions</a>
            <a href="{{ route('shop.wishlist') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">❤ Mes favoris</a>

            <p class="px-3 pb-1 pt-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Catégories</p>
            @foreach ($navCategories as $cat)
                <a href="{{ route('shop.category', $cat) }}" class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50">{{ $cat->name }}</a>
            @endforeach

            <div class="mt-2 border-t border-gray-100 pt-2">
                <a href="{{ route('shop.contact') }}" class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50">Contact</a>
                <a href="{{ route('shop.about') }}" class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50">À propos</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50">Mon compte</a>
                @else
                    <a href="{{ route('shop.order.track') }}" class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50">Suivre ma commande</a>
                @endauth
            </div>
        </nav>
    </div>
</header>
