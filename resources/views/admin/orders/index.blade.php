<x-admin-layout title="Commandes">

    <div class="mb-6 flex flex-wrap items-center gap-2">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="N°, nom, téléphone…"
                   class="w-56 rounded-xl border-gray-200 bg-white text-sm focus:border-primary-500 focus:ring-primary-500">
            <select name="status" x-data @change="$el.form.submit()" class="rounded-xl border-gray-200 bg-white text-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">Filtrer</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/70 text-left text-sm uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Commande</th>
                        <th class="px-6 py-3 font-semibold">Client</th>
                        <th class="px-6 py-3 font-semibold">Ville</th>
                        <th class="px-6 py-3 font-semibold">Total</th>
                        <th class="px-6 py-3 font-semibold">Paiement</th>
                        <th class="px-6 py-3 font-semibold">Statut</th>
                        <th class="px-6 py-3 font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($orders as $order)
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="px-6 py-3.5">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-primary-600 hover:underline">{{ $order->order_number }}</a>
                                <span class="block text-sm text-gray-500">{{ $order->items_count }} article{{ $order->items_count > 1 ? 's' : '' }}</span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="block font-medium">{{ $order->customer_name }}</span>
                                <span class="text-sm text-gray-500">{{ $order->customer_phone }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-gray-500">{{ $order->city }}</td>
                            <td class="px-6 py-3.5 font-semibold">{{ format_price($order->total) }}</td>
                            <td class="px-6 py-3.5">
                                @if ($order->isPaid())
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-600">Payé</span>
                                @else
                                    <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-600">{{ $order->payment_status->label() }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5"><x-order-status-badge :status="$order->status" /></td>
                            <td class="px-6 py-3.5 text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Aucune commande trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>

</x-admin-layout>
