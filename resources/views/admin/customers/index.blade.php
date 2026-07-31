<x-admin-layout title="Clients">

    <form method="GET" class="mb-6 flex items-center gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Nom, email, téléphone…"
               class="w-64 rounded-xl border-gray-200 bg-white text-sm focus:border-primary-500 focus:ring-primary-500">
        <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">Rechercher</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/70 text-left text-xs uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Client</th>
                        <th class="px-6 py-3 font-semibold">Téléphone</th>
                        <th class="px-6 py-3 font-semibold">Commandes</th>
                        <th class="px-6 py-3 font-semibold">Total dépensé</th>
                        <th class="px-6 py-3 font-semibold">Inscrit le</th>
                        <th class="px-6 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($customers as $customer)
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-full bg-primary-600 text-xs font-bold text-white">
                                        {{ strtoupper(mb_substr($customer->name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-semibold">{{ $customer->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $customer->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-gray-500">{{ $customer->phone ?? '—' }}</td>
                            <td class="px-6 py-3.5 text-gray-500">{{ $customer->orders_count }}</td>
                            <td class="px-6 py-3.5 font-semibold">{{ format_price($customer->total_spent ?? 0) }}</td>
                            <td class="px-6 py-3.5 text-gray-400">{{ $customer->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-3.5 text-right">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-primary-600 transition hover:bg-primary-50">Historique</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Aucun client.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $customers->links() }}</div>

</x-admin-layout>
