<x-shop-layout title="Commande confirmée" :whatsappMessage="'Bonjour ! J\'ai une question sur ma commande '.$order->order_number">

    <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:px-8">

        <div class="text-center">
            <span class="mx-auto mb-5 grid h-16 w-16 place-items-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            </span>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Merci pour votre commande !</h1>
            <p class="mt-2 text-sm text-gray-500">
                Votre commande <strong class="text-gray-900">{{ $order->order_number }}</strong> a bien été enregistrée.
                Nous vous contacterons au <strong class="text-gray-900">{{ $order->customer_phone }}</strong> pour la confirmer.
            </p>
        </div>

        <div class="mt-10">
            @include('partials.shop.order-details')
        </div>

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <x-btn href="{{ route('shop.catalog') }}" size="lg">Continuer mes achats</x-btn>
            <a href="{{ whatsapp_link('Bonjour ! J\'ai une question sur ma commande '.$order->order_number) }}" target="_blank" rel="noopener"
               class="rounded-full border-2 border-emerald-500 px-7 py-3 text-center text-sm font-semibold text-emerald-600 transition hover:bg-emerald-500 hover:text-white">
                Nous contacter sur WhatsApp
            </a>
        </div>

        @guest
            <p class="mt-8 text-center text-xs text-gray-400">
                <a href="{{ route('register') }}" class="underline hover:text-gray-600">Créez un compte</a>
                avec l'email {{ $order->customer_email ?: 'utilisé' }} pour suivre vos prochaines commandes.
            </p>
        @endguest
    </div>

</x-shop-layout>
