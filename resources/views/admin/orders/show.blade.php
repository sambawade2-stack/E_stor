<x-admin-layout :title="'Commande '.$order->order_number">

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-gray-500 hover:text-primary-600">← Toutes les commandes</a>
        <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank"
           class="rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700">
            🖨 Imprimer la facture
        </a>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">

        <div class="space-y-6 xl:col-span-2">
            {{-- Articles --}}
            <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
                <h2 class="border-b border-gray-100 px-6 py-4 font-bold">Articles</h2>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-6 py-3.5">
                                    @if ($item->product)
                                        <a href="{{ route('shop.product', $item->product) }}" target="_blank" class="font-medium hover:text-primary-600">{{ $item->product_name }}</a>
                                    @else
                                        <span class="font-medium">{{ $item->product_name }}</span>
                                    @endif
                                    <span class="block text-xs text-gray-400">{{ $item->sku }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-gray-500">{{ format_price($item->unit_price) }} × {{ $item->quantity }}</td>
                                <td class="px-6 py-3.5 text-right font-semibold">{{ format_price($item->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <dl class="space-y-2 border-t border-gray-100 bg-gray-50/40 px-6 py-4 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Sous-total</dt><dd class="font-medium">{{ format_price($order->subtotal) }}</dd></div>
                    @if ($order->discount > 0)
                        <div class="flex justify-between text-emerald-600"><dt>Remise @if($order->coupon_code)({{ $order->coupon_code }})@endif</dt><dd>−{{ format_price($order->discount) }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-gray-500">Livraison</dt><dd class="font-medium">{{ format_price($order->shipping_cost) }}</dd></div>
                    <div class="flex justify-between pt-1 text-base font-extrabold"><dt>Total</dt><dd>{{ format_price($order->total) }}</dd></div>
                </dl>
            </section>

            {{-- Paiements --}}
            @if ($order->payments->isNotEmpty())
                <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
                    <h2 class="border-b border-gray-100 px-6 py-4 font-bold">Transactions</h2>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($order->payments as $payment)
                                <tr>
                                    <td class="px-6 py-3">{{ $payment->provider->label() }}</td>
                                    <td class="px-6 py-3 font-mono text-xs text-gray-400">{{ $payment->provider_reference ?? '—' }}</td>
                                    <td class="px-6 py-3">{{ format_price($payment->amount) }}</td>
                                    <td class="px-6 py-3">{{ $payment->status->label() }}</td>
                                    <td class="px-6 py-3 text-gray-400">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endif
        </div>

        {{-- Colonne latérale --}}
        <div class="space-y-6">
            {{-- Statut --}}
            <section class="rounded-2xl border border-gray-100 bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-bold">Statut</h2>
                    <x-order-status-badge :status="$order->status" />
                </div>

                @if ($order->status->allowedTransitions() !== [])
                    <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="flex gap-2">
                        @csrf @method('PATCH')
                        <select name="status" required class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @foreach ($order->status->allowedTransitions() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="shrink-0 rounded-xl bg-primary-600 px-4 text-sm font-semibold text-white transition hover:bg-primary-700">OK</button>
                    </form>
                    <p class="mt-3 text-xs text-gray-400">
                        L'annulation remet automatiquement les articles en stock.
                        Le passage à « Livrée » encaisse le paiement à la livraison.
                    </p>
                @else
                    <p class="text-sm text-gray-400">Statut final — aucune transition possible.</p>
                @endif

                <dl class="mt-5 space-y-1.5 border-t border-gray-100 pt-4 text-xs text-gray-500">
                    <div class="flex justify-between"><dt>Créée le</dt><dd>{{ $order->created_at->format('d/m/Y H:i') }}</dd></div>
                    @if ($order->paid_at)<div class="flex justify-between"><dt>Payée le</dt><dd>{{ $order->paid_at->format('d/m/Y H:i') }}</dd></div>@endif
                    @if ($order->shipped_at)<div class="flex justify-between"><dt>Expédiée le</dt><dd>{{ $order->shipped_at->format('d/m/Y H:i') }}</dd></div>@endif
                    @if ($order->delivered_at)<div class="flex justify-between"><dt>Livrée le</dt><dd>{{ $order->delivered_at->format('d/m/Y H:i') }}</dd></div>@endif
                    @if ($order->cancelled_at)<div class="flex justify-between"><dt>Annulée le</dt><dd>{{ $order->cancelled_at->format('d/m/Y H:i') }}</dd></div>@endif
                </dl>
            </section>

            {{-- Client --}}
            <section class="rounded-2xl border border-gray-100 bg-white p-6 text-sm">
                <h2 class="mb-4 font-bold">Client</h2>
                <p class="font-semibold">{{ $order->customer_name }}</p>
                <p class="mt-1 text-gray-500">{{ $order->customer_phone }}</p>
                @if ($order->customer_email)
                    <p class="text-gray-500">{{ $order->customer_email }}</p>
                @endif
                <p class="mt-3 text-gray-600">{{ $order->address }}, {{ $order->city }}</p>
                @if ($order->notes)
                    <p class="mt-3 rounded-xl bg-amber-50 px-4 py-3 text-xs text-amber-700">📝 {{ $order->notes }}</p>
                @endif

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ 'https://wa.me/'.preg_replace('/\D/', '', $order->customer_phone).'?text='.rawurlencode('Bonjour '.$order->customer_name.', concernant votre commande '.$order->order_number.' chez '.setting('shop_name').' :') }}"
                       target="_blank" rel="noopener"
                       class="rounded-full bg-emerald-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-600">
                        WhatsApp
                    </a>
                    @if ($order->user)
                        <a href="{{ route('admin.customers.show', $order->user) }}"
                           class="rounded-full border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-50">
                            Fiche client
                        </a>
                    @else
                        <span class="rounded-full bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-500">Commande invité</span>
                    @endif
                </div>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-6 text-sm">
                <h2 class="mb-3 font-bold">Paiement</h2>
                <p class="text-gray-600">{{ $order->payment_provider?->label() ?? '—' }}</p>
                <p class="mt-1">
                    @if ($order->isPaid())
                        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-600">Payé</span>
                    @else
                        <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-600">{{ $order->payment_status->label() }}</span>
                    @endif
                </p>
            </section>
        </div>
    </div>

</x-admin-layout>
