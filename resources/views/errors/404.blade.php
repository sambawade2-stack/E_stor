<x-shop-layout title="Page introuvable">

    <div class="mx-auto flex max-w-2xl flex-col items-center px-4 py-24 text-center sm:px-6">
        <p class="font-display text-8xl font-extrabold tracking-tight">
            4<span class="bg-gradient-to-r from-primary-500 to-accent-400 bg-clip-text text-transparent">0</span>4
        </p>
        <h1 class="mt-4 text-2xl font-extrabold tracking-tight">Oups, cette page n'existe pas</h1>
        <p class="mt-2 max-w-md text-sm leading-relaxed text-gray-500">
            Le produit ou la page que vous cherchez a peut-être été déplacé ou n'est plus disponible.
        </p>

        <form action="{{ route('shop.catalog') }}" method="GET" class="mt-8 w-full max-w-md">
            <div class="relative">
                <input type="search" name="q" placeholder="Rechercher un produit…"
                       class="w-full rounded-full border-gray-200 bg-gray-50 py-3 pl-5 pr-12 text-sm placeholder-gray-400 focus:border-primary-500 focus:bg-white focus:ring-primary-500">
                <button type="submit" aria-label="Rechercher"
                        class="absolute inset-y-0 right-0 my-1 mr-1 grid w-10 place-items-center rounded-full bg-primary-600 text-white transition hover:bg-primary-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </button>
            </div>
        </form>

        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <x-btn href="{{ route('shop.home') }}">Retour à l'accueil</x-btn>
            <x-btn href="{{ route('shop.catalog') }}" variant="outline">Voir le catalogue</x-btn>
        </div>
    </div>

</x-shop-layout>
