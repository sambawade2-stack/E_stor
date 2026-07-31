<x-shop-layout title="Panier" metaDescription="Votre panier — Électroniques Stores.">

    <div class="border-b border-gray-100 bg-gray-50/70">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Mon panier</h1>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

        @if ($items->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-200 py-20 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                <p class="mt-4 font-semibold text-gray-900">Votre panier est vide</p>
                <p class="mt-1 text-sm text-gray-500">Parcourez notre catalogue pour trouver votre bonheur.</p>
                <a href="{{ route('shop.catalog') }}" class="mt-5 inline-block rounded-full bg-primary-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700">
                    Voir le catalogue
                </a>
            </div>
        @else
            <div class="grid gap-8 lg:grid-cols-[1fr_22rem]">

                {{-- Articles --}}
                <div class="space-y-4">
                    @foreach ($items as $item)
                        @php $product = $item['product']; @endphp
                        <div class="flex gap-4 rounded-2xl border border-gray-100 p-4">
                            <a href="{{ route('shop.product', $product) }}" class="block h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-gray-50">
                                <img src="{{ $product->primaryImage?->url() ?? asset('images/placeholder-product.svg') }}"
                                     alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover">
                            </a>

                            <div class="flex min-w-0 flex-1 flex-col">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs uppercase tracking-wide text-gray-400">{{ $product->category->name }}</p>
                                        <a href="{{ route('shop.product', $product) }}" class="mt-0.5 line-clamp-2 text-sm font-semibold text-gray-900 hover:text-primary-600">
                                            {{ $product->name }}
                                        </a>
                                    </div>
                                    <form action="{{ route('shop.cart.remove', $product) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" aria-label="Retirer" class="grid h-8 w-8 place-items-center rounded-full text-gray-400 transition hover:bg-red-50 hover:text-red-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>

                                <div class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-2">
                                    <form action="{{ route('shop.cart.update', $product) }}" method="POST" class="flex items-center rounded-full border border-gray-200">
                                        @csrf @method('PATCH')
                                        <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}" aria-label="Diminuer"
                                                class="grid h-9 w-9 place-items-center text-gray-500 transition hover:text-primary-600">−</button>
                                        <span class="w-8 text-center text-sm font-semibold">{{ $item['quantity'] }}</span>
                                        <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" aria-label="Augmenter"
                                                @disabled($item['quantity'] >= $product->stock_quantity)
                                                class="grid h-9 w-9 place-items-center text-gray-500 transition hover:text-primary-600 disabled:opacity-30">+</button>
                                    </form>

                                    <div class="text-right">
                                        <p class="text-sm font-bold text-gray-900">{{ format_price($item['line_total']) }}</p>
                                        <p class="text-xs text-gray-400">{{ format_price($product->current_price) }} / unité</p>
                                    </div>
                                </div>

                                @if ($item['quantity'] >= $product->stock_quantity)
                                    <p class="mt-1.5 text-xs font-medium text-amber-600">Stock maximum atteint ({{ $product->stock_quantity }})</p>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <a href="{{ route('shop.catalog') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600 hover:text-primary-700">
                        ← Continuer mes achats
                    </a>
                </div>

                {{-- Récapitulatif --}}
                <aside class="h-fit space-y-5 rounded-2xl border border-gray-100 bg-gray-50/60 p-6">
                    <h2 class="text-lg font-bold">Récapitulatif</h2>

                    {{-- Coupon --}}
                    @if ($coupon)
                        <div class="flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3 text-sm">
                            <span class="font-semibold text-emerald-700">Code {{ $coupon->code }} appliqué</span>
                            <form action="{{ route('shop.cart.coupon.remove') }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-emerald-600 underline hover:text-emerald-800">Retirer</button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('shop.cart.coupon') }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="text" name="code" placeholder="Code promo" value="{{ old('code') }}"
                                   class="w-full rounded-xl border-gray-200 text-sm uppercase placeholder:normal-case focus:border-primary-500 focus:ring-primary-500">
                            <button type="submit" class="shrink-0 rounded-xl bg-gray-900 px-4 text-sm font-semibold text-white transition hover:bg-gray-700">OK</button>
                        </form>
                        @error('code')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    @endif

                    {{-- Estimation livraison --}}
                    <form action="{{ route('shop.cart.shipping') }}" method="POST">
                        @csrf
                        <label for="shipping_zone_id" class="mb-1.5 block text-sm font-medium text-gray-700">Estimer la livraison</label>
                        <select id="shipping_zone_id" name="shipping_zone_id" onchange="this.form.submit()"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="" disabled @selected(! $zone)>Choisir ma zone…</option>
                            @foreach ($zones as $z)
                                <option value="{{ $z->id }}" @selected($zone?->id === $z->id)>
                                    {{ $z->name }} — {{ format_price($z->cost) }} ({{ $z->delivery_delay }})
                                </option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Totaux --}}
                    <dl class="space-y-2.5 border-t border-gray-200 pt-4 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Sous-total</dt>
                            <dd class="font-medium">{{ format_price($subtotal) }}</dd>
                        </div>
                        @if ($discount > 0)
                            <div class="flex justify-between text-emerald-600">
                                <dt>Remise</dt>
                                <dd class="font-medium">−{{ format_price($discount) }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Livraison</dt>
                            <dd class="font-medium">{{ $zone ? format_price($shippingCost) : 'À estimer' }}</dd>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-2.5 text-base font-extrabold">
                            <dt>Total</dt>
                            <dd>{{ format_price($total) }}</dd>
                        </div>
                    </dl>

                    <a href="{{ route('shop.checkout') }}"
                       class="block rounded-full bg-primary-600 py-3.5 text-center text-sm font-semibold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700">
                        Passer la commande
                    </a>
                    <p class="text-center text-xs text-gray-400">Paiement sécurisé · Livraison rapide</p>
                </aside>
            </div>
        @endif
    </div>

</x-shop-layout>
