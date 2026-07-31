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

        <div class="mt-10 overflow-hidden rounded-2xl border border-gray-100">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/70 px-6 py-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-400">Commande</p>
                    <p class="font-bold">{{ $order->order_number }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-400">Date</p>
                    <p class="text-sm font-medium">{{ $order->created_at->translatedFormat('d F Y à H:i') }}</p>
                </div>
                <x-order-status-badge :status="$order->status" />
            </div>

            <ul class="divide-y divide-gray-100 px-6">
                @foreach ($order->items as $item)
                    <li class="flex items-center justify-between gap-4 py-3.5 text-sm">
                        <span class="min-w-0">
                            <span class="line-clamp-1 font-medium text-gray-900">{{ $item->product_name }}</span>
                            <span class="text-xs text-gray-400">{{ format_price($item->unit_price) }} × {{ $item->quantity }}</span>
                        </span>
                        <span class="shrink-0 font-semibold">{{ format_price($item->total) }}</span>
                    </li>
                @endforeach
            </ul>

            <dl class="space-y-2 border-t border-gray-100 bg-gray-50/40 px-6 py-4 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Sous-total</dt><dd class="font-medium">{{ format_price($order->subtotal) }}</dd></div>
                @if ($order->discount > 0)
                    <div class="flex justify-between text-emerald-600"><dt>Remise @if($order->coupon_code)({{ $order->coupon_code }})@endif</dt><dd class="font-medium">−{{ format_price($order->discount) }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-gray-500">Livraison ({{ $order->city }})</dt><dd class="font-medium">{{ format_price($order->shipping_cost) }}</dd></div>
                <div class="flex justify-between pt-1 text-base font-extrabold"><dt>Total</dt><dd>{{ format_price($order->total) }}</dd></div>
            </dl>
        </div>

        <div class="mt-8 rounded-2xl border border-gray-100 p-6 text-sm">
            <h2 class="mb-3 font-bold">Livraison</h2>
            <p class="text-gray-600">{{ $order->customer_name }} — {{ $order->customer_phone }}</p>
            <p class="text-gray-600">{{ $order->address }}, {{ $order->city }}</p>
            @if ($order->notes)
                <p class="mt-2 text-xs text-gray-400">Note : {{ $order->notes }}</p>
            @endif
            <p class="mt-3 rounded-xl bg-primary-50 px-4 py-3 text-primary-700">
                Mode de paiement : <strong>{{ $order->payment_provider->label() }}</strong>
            </p>
        </div>

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('shop.catalog') }}" class="rounded-full bg-primary-600 px-7 py-3 text-center text-sm font-semibold text-white transition hover:bg-primary-700">
                Continuer mes achats
            </a>
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
