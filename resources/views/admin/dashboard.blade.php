<x-admin-layout title="Tableau de bord">

    {{-- Statistiques clés --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Chiffre d'affaires</p>
            <p class="mt-1 text-2xl font-extrabold">{{ format_price($revenue) }}</p>
            <p class="mt-1 text-xs text-gray-400">dont {{ format_price($revenueThisMonth) }} ce mois-ci</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Commandes</p>
            <p class="mt-1 text-2xl font-extrabold">{{ $ordersCount }}</p>
            <p class="mt-1 text-xs {{ $pendingOrdersCount > 0 ? 'font-semibold text-amber-600' : 'text-gray-400' }}">
                {{ $pendingOrdersCount }} en attente
            </p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Clients</p>
            <p class="mt-1 text-2xl font-extrabold">{{ $customersCount }}</p>
            <p class="mt-1 text-xs text-gray-400">comptes et invités</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Produits</p>
            <p class="mt-1 text-2xl font-extrabold">{{ $productsCount }}</p>
            <p class="mt-1 text-xs {{ $lowStockProducts->isNotEmpty() ? 'font-semibold text-red-600' : 'text-gray-400' }}">
                {{ $lowStockProducts->count() }} en stock faible
            </p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">

        {{-- Ventes mensuelles --}}
        <section class="rounded-2xl border border-gray-100 bg-white p-6 xl:col-span-2">
            <h2 class="mb-6 font-bold">Ventes des 12 derniers mois</h2>
            @php $maxSale = max($monthlySales->max(), 1); @endphp
            <div class="flex h-48 items-end gap-1.5 sm:gap-2.5">
                @foreach ($monthlySales as $month => $amount)
                    <div class="group relative flex h-full flex-1 flex-col justify-end">
                        <div class="pointer-events-none absolute -top-1 left-1/2 z-10 hidden -translate-x-1/2 -translate-y-full whitespace-nowrap rounded-lg bg-gray-900 px-2.5 py-1.5 text-xs font-semibold text-white group-hover:block">
                            {{ format_price($amount) }}
                        </div>
                        <div class="rounded-t-md {{ $amount > 0 ? 'bg-primary-600 group-hover:bg-primary-500' : 'bg-gray-100' }} transition"
                             style="height: {{ $amount > 0 ? max(round($amount / $maxSale * 100), 4) : 4 }}%"></div>
                        <p class="mt-2 truncate text-center text-[10px] font-medium text-gray-400">{{ $month }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Produits populaires --}}
        <section class="rounded-2xl border border-gray-100 bg-white p-6">
            <h2 class="mb-4 font-bold">Produits les plus vendus</h2>
            @if ($topProducts->isEmpty())
                <p class="text-sm text-gray-400">Aucune vente pour le moment.</p>
            @else
                <ol class="space-y-3">
                    @foreach ($topProducts as $index => $top)
                        <li class="flex items-center gap-3 text-sm">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-gray-100 text-xs font-bold text-gray-500">{{ $index + 1 }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="line-clamp-1 font-medium">{{ $top->product_name }}</span>
                                <span class="text-xs text-gray-400">{{ $top->total_sold }} vendus · {{ format_price($top->total_revenue) }}</span>
                            </span>
                        </li>
                    @endforeach
                </ol>
            @endif

            @if ($lowStockProducts->isNotEmpty())
                <h2 class="mb-3 mt-6 font-bold text-red-600">Stock faible</h2>
                <ul class="space-y-2">
                    @foreach ($lowStockProducts as $product)
                        <li class="flex items-center justify-between gap-3 text-sm">
                            <a href="{{ route('admin.products.edit', $product) }}" class="line-clamp-1 min-w-0 font-medium hover:text-primary-600">{{ $product->name }}</a>
                            <span class="shrink-0 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-bold text-red-600">{{ $product->stock_quantity }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    {{-- Dernières commandes --}}
    <section class="mt-6 overflow-hidden rounded-2xl border border-gray-100 bg-white">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="font-bold">Dernières commandes</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700">Tout voir →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-y border-gray-100 bg-gray-50/70 text-left text-xs uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Commande</th>
                        <th class="px-6 py-3 font-semibold">Client</th>
                        <th class="px-6 py-3 font-semibold">Articles</th>
                        <th class="px-6 py-3 font-semibold">Total</th>
                        <th class="px-6 py-3 font-semibold">Statut</th>
                        <th class="px-6 py-3 font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($recentOrders as $order)
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="px-6 py-3.5">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-primary-600 hover:underline">{{ $order->order_number }}</a>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="block font-medium">{{ $order->customer_name }}</span>
                                <span class="text-xs text-gray-400">{{ $order->customer_phone }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-gray-500">{{ $order->items_count }}</td>
                            <td class="px-6 py-3.5 font-semibold">{{ format_price($order->total) }}</td>
                            <td class="px-6 py-3.5"><x-order-status-badge :status="$order->status" /></td>
                            <td class="px-6 py-3.5 text-gray-400">{{ $order->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Aucune commande.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</x-admin-layout>
