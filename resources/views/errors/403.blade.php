<x-shop-layout title="Accès refusé">

    <div class="mx-auto flex max-w-2xl flex-col items-center px-4 py-24 text-center sm:px-6">
        <p class="font-display text-8xl font-extrabold tracking-tight">
            4<span class="bg-gradient-to-r from-primary-500 to-accent-400 bg-clip-text text-transparent">0</span>3
        </p>
        <h1 class="mt-4 text-2xl font-extrabold tracking-tight">Accès refusé</h1>
        <p class="mt-2 max-w-md text-sm leading-relaxed text-gray-500">
            Vous n'avez pas les autorisations nécessaires pour accéder à cette page.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <x-btn href="{{ route('shop.home') }}">Retour à l'accueil</x-btn>
            @guest
                <x-btn href="{{ route('login') }}" variant="outline">Se connecter</x-btn>
            @endguest
        </div>
    </div>

</x-shop-layout>
