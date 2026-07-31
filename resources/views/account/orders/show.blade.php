<x-shop-layout :title="'Commande '.$order->order_number"
    :whatsappMessage="'Bonjour ! J\'ai une question sur ma commande '.$order->order_number">

    <div class="border-b border-gray-100 bg-gray-50/70">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <nav class="mb-2 text-xs text-gray-400" aria-label="Fil d'Ariane">
                <a href="{{ route('dashboard') }}" class="hover:text-primary-600">Mon compte</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('account.orders') }}" class="hover:text-primary-600">Mes commandes</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-600">{{ $order->order_number }}</span>
            </nav>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Commande {{ $order->order_number }}</h1>
        </div>
    </div>

    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        @include('partials.shop.order-details')

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('account.orders') }}"
               class="rounded-full border border-gray-200 px-7 py-3 text-center text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                ← Mes commandes
            </a>
            <a href="{{ whatsapp_link('Bonjour ! J\'ai une question sur ma commande '.$order->order_number) }}" target="_blank" rel="noopener"
               class="rounded-full border-2 border-emerald-500 px-7 py-3 text-center text-sm font-semibold text-emerald-600 transition hover:bg-emerald-500 hover:text-white">
                Question sur cette commande ?
            </a>
        </div>
    </div>

</x-shop-layout>
