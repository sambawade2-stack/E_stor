<x-admin-layout :title="'Client : '.$customer->name">

    <a href="{{ route('admin.customers.index') }}" class="mb-6 inline-block text-sm font-semibold text-gray-500 hover:text-primary-600">← Tous les clients</a>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-100 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Contact</p>
            <p class="mt-1 font-bold">{{ $customer->name }}</p>
            <p class="text-sm text-gray-500">{{ $customer->email }}</p>
            <p class="text-sm text-gray-500">{{ $customer->phone ?? 'Téléphone non renseigné' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Commandes</p>
            <p class="mt-1 text-2xl font-extrabold">{{ $customer->orders_count }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Total dépensé</p>
            <p class="mt-1 text-2xl font-extrabold">{{ format_price($customer->total_spent ?? 0) }}</p>
            <p class="mt-1 text-xs text-gray-400">hors commandes annulées</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
        <h2 class="border-b border-gray-100 px-6 py-4 font-bold">Historique des commandes</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-50">
                    @forelse ($orders as $order)
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="px-6 py-3.5">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-primary-600 hover:underline">{{ $order->order_number }}</a>
                            </td>
                            <td class="px-6 py-3.5 text-gray-500">{{ $order->items_count }} article{{ $order->items_count > 1 ? 's' : '' }}</td>
                            <td class="px-6 py-3.5 font-semibold">{{ format_price($order->total) }}</td>
                            <td class="px-6 py-3.5"><x-order-status-badge :status="$order->status" /></td>
                            <td class="px-6 py-3.5 text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-6 py-10 text-center text-gray-400">Aucune commande.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>

</x-admin-layout>
