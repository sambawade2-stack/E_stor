<x-shop-layout title="Mes commandes">

    <div class="border-b border-gray-100 bg-gray-50/70">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Mes commandes</h1>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[15rem_1fr]">

            <aside class="h-fit rounded-2xl border border-gray-100 p-3">
                @include('partials.shop.account-nav')
            </aside>

            <div>
                @if ($orders->isEmpty())
                    <div class="rounded-2xl border border-dashed border-gray-200 py-20 text-center">
                        <p class="font-semibold text-gray-900">Aucune commande pour le moment</p>
                        <p class="mt-1 text-sm text-gray-500">Vos commandes apparaîtront ici après votre premier achat.</p>
                        <x-btn href="{{ route('shop.catalog') }}" class="mt-5">Découvrir le catalogue</x-btn>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-100">
                        @foreach ($orders as $order)
                            <li>
                                <a href="{{ route('account.orders.show', $order) }}"
                                   class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 transition hover:bg-gray-50/70">
                                    <span>
                                        <span class="block text-sm font-bold">{{ $order->order_number }}</span>
                                        <span class="block text-xs text-gray-400">
                                            {{ $order->created_at->translatedFormat('d F Y') }} · {{ $order->items_count }} article{{ $order->items_count > 1 ? 's' : '' }}
                                        </span>
                                    </span>
                                    <span class="flex items-center gap-3">
                                        <span class="text-sm font-semibold">{{ format_price($order->total) }}</span>
                                        <x-order-status-badge :status="$order->status" />
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-shop-layout>
