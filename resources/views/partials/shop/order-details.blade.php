{{-- Détail complet d'une commande : $order avec ses items chargés --}}
<div class="overflow-hidden rounded-2xl border border-gray-100">
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
            <li class="flex items-center gap-3.5 py-3.5 text-sm">
                <img src="{{ $item->imageUrl() }}" alt="{{ $item->product_name }}" loading="lazy"
                     class="h-14 w-14 shrink-0 rounded-lg border border-gray-100 bg-gray-50 object-cover">
                <span class="min-w-0 flex-1">
                    <span class="line-clamp-2 font-medium text-gray-900">{{ $item->product_name }}</span>
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
        <div class="flex justify-between"><dt class="text-gray-500">Livraison ({{ $order->city }})</dt><dd class="font-medium">{{ $order->shippingLabel() }}</dd></div>
        <div class="flex justify-between pt-1 text-base font-extrabold"><dt>Total</dt><dd>{{ format_price($order->total) }}</dd></div>
    </dl>
</div>

<div class="mt-6 rounded-2xl border border-gray-100 p-6 text-sm">
    <h2 class="mb-3 font-bold">Livraison</h2>
    <p class="text-gray-600">{{ $order->customer_name }} — {{ $order->customer_phone }}</p>
    <p class="text-gray-600">{{ $order->address }}, {{ $order->city }}</p>
    @if ($order->notes)
        <p class="mt-2 text-xs text-gray-400">Note : {{ $order->notes }}</p>
    @endif
    @if ($order->payment_provider)
        <p class="mt-3 rounded-xl bg-primary-50 px-4 py-3 text-primary-700">
            Mode de paiement : <strong>{{ $order->payment_provider->label() }}</strong>
            @if ($order->isPaid())
                <span class="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">Payé</span>
            @endif
        </p>
    @endif
</div>
