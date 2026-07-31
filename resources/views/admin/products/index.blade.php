<x-admin-layout title="Produits">

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher nom, SKU…"
                   class="w-56 rounded-xl border-gray-200 bg-white text-sm focus:border-primary-500 focus:ring-primary-500">
            <select name="category" onchange="this.form.submit()" class="rounded-xl border-gray-200 bg-white text-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="">Toutes les catégories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="state" onchange="this.form.submit()" class="rounded-xl border-gray-200 bg-white text-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les produits</option>
                <option value="inactive" @selected(request('state') === 'inactive')>Inactifs</option>
                <option value="low_stock" @selected(request('state') === 'low_stock')>Stock faible</option>
            </select>
            <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">Filtrer</button>
        </form>

        <a href="{{ route('admin.products.create') }}"
           class="rounded-full bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700">
            + Nouveau produit
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/70 text-left text-xs uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Produit</th>
                        <th class="px-6 py-3 font-semibold">Catégorie</th>
                        <th class="px-6 py-3 font-semibold">Prix</th>
                        <th class="px-6 py-3 font-semibold">Stock</th>
                        <th class="px-6 py-3 font-semibold">Statut</th>
                        <th class="px-6 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($products as $product)
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $product->primaryImage?->url() ?? asset('images/placeholder-product.svg') }}"
                                         alt="" class="h-11 w-11 rounded-lg border border-gray-100 object-cover">
                                    <div class="min-w-0">
                                        <p class="line-clamp-1 font-semibold">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $product->sku }}@if($product->brand) · {{ $product->brand->name }}@endif</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ $product->category->name }}</td>
                            <td class="px-6 py-3">
                                <span class="font-semibold">{{ format_price($product->current_price) }}</span>
                                @if ($product->isOnSale())
                                    <span class="block text-xs text-gray-400 line-through">{{ format_price($product->price) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="font-semibold {{ $product->stock_quantity === 0 ? 'text-red-600' : ($product->isLowStock() ? 'text-amber-600' : '') }}">
                                    {{ $product->stock_quantity }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @if ($product->is_active)
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-600">Actif</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-500">Inactif</span>
                                    @endif
                                    @if ($product->is_featured)
                                        <span class="rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-semibold text-primary-600">Vedette</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('shop.product', $product) }}" target="_blank" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-gray-500 transition hover:bg-gray-100" title="Voir sur la boutique">Voir</a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-primary-600 transition hover:bg-primary-50">Modifier</a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                          onsubmit="return confirm('Supprimer ce produit ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-500 transition hover:bg-red-50">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Aucun produit trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $products->links() }}</div>

</x-admin-layout>
