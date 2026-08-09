<x-shop-layout title="Commander" metaDescription="Finalisez votre commande en une seule étape — Électroniques Stores.">

    <div class="border-b border-gray-100 bg-gray-50/70">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Finaliser ma commande</h1>
            <p class="mt-1 text-sm text-gray-500">Une seule étape — aucun compte requis.</p>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
         x-data="{ zone: '{{ old('shipping_zone_id', $zone?->id) }}',
                   subtotal: {{ $subtotal }},
                   discount: {{ $discount }},
                   get total() { return this.subtotal - this.discount },
                   fmt(n) { return new Intl.NumberFormat('fr-FR').format(n) + ' {{ setting('currency_symbol') }}' } }">

        <form action="{{ route('shop.checkout.store') }}" method="POST" class="grid gap-8 lg:grid-cols-[1fr_24rem]">
            @csrf

            {{-- Informations client --}}
            <div class="space-y-6">
                <section class="rounded-2xl border border-gray-100 p-6">
                    <h2 class="mb-5 text-lg font-bold">Vos informations</h2>

                    @guest
                        <p class="mb-5 rounded-xl bg-primary-50 px-4 py-3 text-sm text-primary-700">
                            Aucun compte n'est nécessaire. Renseignez simplement vos coordonnées de livraison —
                            vous pourrez suivre votre commande avec son numéro et votre téléphone.
                        </p>
                    @endguest

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="customer_name" class="mb-1.5 block text-sm font-medium text-gray-700">Nom complet *</label>
                            <input type="text" id="customer_name" name="customer_name" required
                                   value="{{ old('customer_name', auth()->user()?->name) }}"
                                   class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('customer_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="customer_phone" class="mb-1.5 block text-sm font-medium text-gray-700">Téléphone *</label>
                            <input type="tel" id="customer_phone" name="customer_phone" required placeholder="+221 77 000 00 00"
                                   value="{{ old('customer_phone', auth()->user()?->phone) }}"
                                   class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('customer_phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="customer_email" class="mb-1.5 block text-sm font-medium text-gray-700">Email <span class="text-gray-400">(facultatif — pour recevoir la confirmation)</span></label>
                            <input type="email" id="customer_email" name="customer_email"
                                   value="{{ old('customer_email', auth()->user()?->email) }}"
                                   class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('customer_email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-gray-100 p-6">
                    <h2 class="mb-5 text-lg font-bold">Livraison</h2>
                    <div class="grid gap-5">
                        <div>
                            <label for="shipping_zone_id" class="mb-1.5 block text-sm font-medium text-gray-700">Ville / Zone de livraison *</label>
                            <select id="shipping_zone_id" name="shipping_zone_id" required x-model="zone"
                                    class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="" disabled>Choisir…</option>
                                @foreach ($zones as $z)
                                    <option value="{{ $z->id }}">{{ $z->name }} ({{ $z->delivery_delay }})</option>
                                @endforeach
                            </select>
                            @error('shipping_zone_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="address" class="mb-1.5 block text-sm font-medium text-gray-700">Adresse complète *</label>
                            <input type="text" id="address" name="address" required placeholder="Quartier, rue, repère…"
                                   value="{{ old('address') }}"
                                   class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('address')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="notes" class="mb-1.5 block text-sm font-medium text-gray-700">Notes <span class="text-gray-400">(facultatif)</span></label>
                            <textarea id="notes" name="notes" rows="3" placeholder="Instructions de livraison, précisions…"
                                      class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('notes') }}</textarea>
                            @error('notes')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-gray-100 p-6">
                    <h2 class="mb-5 text-lg font-bold">Paiement</h2>
                    <div class="space-y-3" x-data="{ payment: '{{ old('payment', 'cash_on_delivery') }}' }">
                        @foreach ($paymentMethods as $method)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border-2 p-4 transition"
                                   :class="payment === '{{ $method->provider()->value }}' ? 'border-primary-600 bg-primary-50/50' : 'border-gray-100 hover:border-gray-200'">
                                <input type="radio" name="payment" value="{{ $method->provider()->value }}" x-model="payment"
                                       class="text-primary-600 focus:ring-primary-500">
                                <span>
                                    <span class="block text-sm font-semibold">{{ $method->label() }}</span>
                                    <span class="block text-xs text-gray-500">{{ $method->description() }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('payment')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
                    @if ($paymentMethods->count() === 1)
                        <p class="mt-3 text-xs text-gray-400">Le paiement en ligne (Wave, Orange Money, carte bancaire) arrive très bientôt.</p>
                    @endif
                </section>
            </div>

            {{-- Récapitulatif --}}
            <aside class="h-fit space-y-5 rounded-2xl border border-gray-100 bg-gray-50/60 p-6">
                <h2 class="text-lg font-bold">Votre commande</h2>

                <ul class="divide-y divide-gray-200/70">
                    @foreach ($items as $item)
                        <li class="flex items-center gap-3 py-3">
                            <span class="relative block h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                <img src="{{ $item['product']->primaryImage?->url() ?? asset('images/placeholder-product.svg') }}"
                                     alt="{{ $item['product']->name }}" loading="lazy" class="h-full w-full object-cover">
                                <span class="absolute -right-0 -top-0 grid h-5 min-w-5 place-items-center rounded-full bg-gray-900 px-1 text-[10px] font-bold text-white">{{ $item['quantity'] }}</span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="line-clamp-2 text-xs font-medium text-gray-700">{{ $item['product']->name }}</span>
                            </span>
                            <span class="shrink-0 text-sm font-semibold">{{ format_price($item['line_total']) }}</span>
                        </li>
                    @endforeach
                </ul>

                <dl class="space-y-2.5 border-t border-gray-200 pt-4 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Sous-total</dt>
                        <dd class="font-medium" x-text="fmt(subtotal)"></dd>
                    </div>
                    @if ($discount > 0)
                        <div class="flex justify-between text-emerald-600">
                            <dt>Remise ({{ $coupon->code }})</dt>
                            <dd class="font-medium" x-text="'−' + fmt(discount)"></dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Livraison</dt>
                        <dd class="font-medium text-gray-500">À convenir</dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-2.5 text-base font-extrabold">
                        <dt>Total</dt>
                        <dd x-text="fmt(total)"></dd>
                    </div>
                </dl>

                <x-btn size="lg" class="btn-shine w-full">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                    Confirmer ma commande
                </x-btn>
                <p class="text-center text-xs text-gray-400">
                    En confirmant, vous acceptez nos
                    <a href="{{ route('shop.terms') }}" class="underline hover:text-gray-600">conditions générales</a>.
                </p>
            </aside>
        </form>
    </div>

</x-shop-layout>
