@php
    use App\Enums\OrderStatus;

    // Étapes réelles, datées d'après les horodatages de la commande.
    $steps = [
        ['label' => 'Commande reçue', 'at' => $order->created_at],
        ['label' => 'Paiement confirmé', 'at' => $order->paid_at],
        ['label' => 'Expédiée', 'at' => $order->shipped_at],
        ['label' => 'Livrée', 'at' => $order->delivered_at],
    ];

    $cancelled = $order->status === OrderStatus::Cancelled;
@endphp

<x-shop-layout :title="'Commande '.$order->order_number"
               :whatsappMessage="'Bonjour ! J\'ai une question sur ma commande '.$order->order_number">

    <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:px-8">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-400">Suivi de commande</p>
                <h1 class="text-2xl font-extrabold tracking-tight">{{ $order->order_number }}</h1>
            </div>
            <x-order-status-badge :status="$order->status" />
        </div>

        @if ($cancelled)
            <div class="mt-8 rounded-2xl border border-red-100 bg-red-50 p-6">
                <h2 class="font-bold text-red-800">Commande annulée</h2>
                <p class="mt-1 text-sm text-red-700">
                    Cette commande a été annulée le {{ $order->cancelled_at?->translatedFormat('d F Y') }}.
                    Une question ? Contactez-nous, nous pouvons la relancer.
                </p>
            </div>
        @else
            <ol class="mt-8 space-y-0 rounded-2xl border border-gray-100 p-6">
                @foreach ($steps as $i => $step)
                    @php $done = $step['at'] !== null; @endphp
                    <li class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <span @class([
                                'grid h-8 w-8 shrink-0 place-items-center rounded-full text-xs font-bold',
                                'bg-emerald-500 text-white' => $done,
                                'bg-gray-100 text-gray-400' => ! $done,
                            ])>
                                @if ($done)
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </span>
                            @if (! $loop->last)
                                <span @class([
                                    'my-1 w-0.5 flex-1',
                                    'bg-emerald-500' => $done,
                                    'bg-gray-100' => ! $done,
                                ])></span>
                            @endif
                        </div>
                        <div @class(['pb-6' => ! $loop->last])>
                            <p @class([
                                'text-sm font-semibold',
                                'text-gray-900' => $done,
                                'text-gray-400' => ! $done,
                            ])>{{ $step['label'] }}</p>
                            @if ($done)
                                <p class="text-xs text-gray-400">{{ $step['at']->translatedFormat('d F Y à H:i') }}</p>
                            @else
                                <p class="text-xs text-gray-300">En attente</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif

        <div class="mt-8">
            @include('partials.shop.order-details')
        </div>

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <x-btn href="{{ route('shop.catalog') }}" size="lg">Continuer mes achats</x-btn>
            <a href="{{ whatsapp_link('Bonjour ! J\'ai une question sur ma commande '.$order->order_number) }}" target="_blank" rel="noopener"
               class="rounded-full border-2 border-emerald-500 px-7 py-3 text-center text-sm font-semibold text-emerald-600 transition hover:bg-emerald-500 hover:text-white">
                Nous contacter sur WhatsApp
            </a>
        </div>

    </div>

</x-shop-layout>
