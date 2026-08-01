<x-shop-layout title="Mes favoris" metaDescription="Vos produits favoris — Électroniques Stores.">

    <div class="border-b border-gray-100 bg-gray-50/70">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Mes favoris</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $products->count() }} produit{{ $products->count() > 1 ? 's' : '' }} enregistré{{ $products->count() > 1 ? 's' : '' }}</p>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($products->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-200 py-20 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                <p class="mt-4 font-semibold text-gray-900">Aucun favori pour le moment</p>
                <p class="mt-1 text-sm text-gray-500">Touchez le cœur sur un produit pour le retrouver ici.</p>
                <x-btn href="{{ route('shop.catalog') }}" class="mt-6">Découvrir le catalogue</x-btn>
            </div>
        @else
            <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </div>

</x-shop-layout>
