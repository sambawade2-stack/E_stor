@php $editing = $product->exists; @endphp

<x-admin-layout :title="$editing ? 'Modifier : '.$product->name : 'Nouveau produit'">

    <form action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}"
          method="POST" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-3">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="space-y-6 xl:col-span-2">

            {{-- Informations générales --}}
            <section class="rounded-2xl border border-gray-100 bg-white p-6">
                <h2 class="mb-5 text-lg font-bold">Informations générales</h2>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Nom du produit *</label>
                        <input type="text" id="name" name="name" required value="{{ old('name', $product->name) }}"
                               class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="sku" class="mb-1.5 block text-sm font-medium text-gray-700">SKU *</label>
                        <input type="text" id="sku" name="sku" required value="{{ old('sku', $product->sku) }}"
                               class="w-full rounded-xl border-gray-200 text-sm uppercase focus:border-primary-500 focus:ring-primary-500">
                        @error('sku')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="category_id" class="mb-1.5 block text-sm font-medium text-gray-700">Catégorie *</label>
                        <select id="category_id" name="category_id" required class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="" disabled @selected(! old('category_id', $product->category_id))>Choisir…</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="brand_id" class="mb-1.5 block text-sm font-medium text-gray-700">Marque</label>
                        <select id="brand_id" name="brand_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Aucune</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="short_description" class="mb-1.5 block text-sm font-medium text-gray-700">Description courte</label>
                        <input type="text" id="short_description" name="short_description" maxlength="500"
                               value="{{ old('short_description', $product->short_description) }}"
                               class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('short_description')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700">Description complète</label>
                        <textarea id="description" name="description" rows="6"
                                  class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('description', $product->description) }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="features_text" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Caractéristiques <span class="text-gray-400">(une par ligne, format « Libellé : Valeur »)</span>
                        </label>
                        <textarea id="features_text" name="features_text" rows="4" placeholder="Capacité : 20000 mAh&#10;Connectivité : Bluetooth 5.3"
                                  class="w-full rounded-xl border-gray-200 font-mono text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('features_text', collect($product->features ?? [])->map(fn ($v, $k) => "$k : $v")->implode("\n")) }}</textarea>
                        @error('features_text')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            {{-- Prix & stock --}}
            <section class="rounded-2xl border border-gray-100 bg-white p-6">
                <h2 class="mb-5 text-lg font-bold">Prix & stock</h2>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="price" class="mb-1.5 block text-sm font-medium text-gray-700">Prix ({{ setting('currency_symbol') }}) *</label>
                        <input type="number" id="price" name="price" required min="0" step="1" value="{{ old('price', $product->price === null ? '' : (int) $product->price) }}"
                               class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('price')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="sale_price" class="mb-1.5 block text-sm font-medium text-gray-700">Prix promo</label>
                        <input type="number" id="sale_price" name="sale_price" min="0" step="1" value="{{ old('sale_price', $product->sale_price === null ? '' : (int) $product->sale_price) }}"
                               class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('sale_price')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="sale_starts_at" class="mb-1.5 block text-sm font-medium text-gray-700">Début promo</label>
                        <input type="datetime-local" id="sale_starts_at" name="sale_starts_at"
                               value="{{ old('sale_starts_at', $product->sale_starts_at?->format('Y-m-d\TH:i')) }}"
                               class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label for="sale_ends_at" class="mb-1.5 block text-sm font-medium text-gray-700">Fin promo</label>
                        <input type="datetime-local" id="sale_ends_at" name="sale_ends_at"
                               value="{{ old('sale_ends_at', $product->sale_ends_at?->format('Y-m-d\TH:i')) }}"
                               class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('sale_ends_at')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="stock_quantity" class="mb-1.5 block text-sm font-medium text-gray-700">Stock *</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" required min="0" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}"
                               class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('stock_quantity')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="low_stock_threshold" class="mb-1.5 block text-sm font-medium text-gray-700">Seuil de stock faible</label>
                        <input type="number" id="low_stock_threshold" name="low_stock_threshold" min="0" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}"
                               class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>
            </section>

            {{-- SEO --}}
            <section class="rounded-2xl border border-gray-100 bg-white p-6">
                <h2 class="mb-5 text-lg font-bold">SEO <span class="text-xs font-normal text-gray-400">(facultatif — généré automatiquement sinon)</span></h2>
                <div class="grid gap-5">
                    <div>
                        <label for="meta_title" class="mb-1.5 block text-sm font-medium text-gray-700">Meta title</label>
                        <input type="text" id="meta_title" name="meta_title" maxlength="255" value="{{ old('meta_title', $product->meta_title) }}"
                               class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label for="meta_description" class="mb-1.5 block text-sm font-medium text-gray-700">Meta description</label>
                        <textarea id="meta_description" name="meta_description" rows="2" maxlength="500"
                                  class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('meta_description', $product->meta_description) }}</textarea>
                    </div>
                </div>
            </section>
        </div>

        {{-- Colonne latérale --}}
        <div class="space-y-6">
            <section class="rounded-2xl border border-gray-100 bg-white p-6">
                <h2 class="mb-4 text-lg font-bold">Publication</h2>
                <label class="flex items-center gap-2.5 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    Produit actif (visible sur la boutique)
                </label>
                <label class="mt-3 flex items-center gap-2.5 text-sm">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured ?? false))
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    Produit vedette (mis en avant sur l'accueil)
                </label>

                <div class="mt-6 flex gap-2">
                    <button type="submit" class="flex-1 rounded-full bg-primary-600 py-3 text-sm font-semibold text-white transition hover:bg-primary-700">
                        {{ $editing ? 'Enregistrer' : 'Créer le produit' }}
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="rounded-full border border-gray-200 px-5 py-3 text-sm font-medium text-gray-500 transition hover:bg-gray-50">Annuler</a>
                </div>
            </section>

            {{-- Images --}}
            <section class="rounded-2xl border border-gray-100 bg-white p-6">
                <h2 class="mb-4 text-lg font-bold">Images</h2>

                <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                       class="w-full text-sm text-gray-500 file:mr-3 file:rounded-full file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-600 hover:file:bg-primary-100">
                <p class="mt-2 text-sm text-gray-500">JPG, PNG ou WebP — 10 Mo max par image, 8 images max. Optimisées automatiquement en WebP.</p>
                @error('images.*')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
            </section>
        </div>
    </form>

    {{-- Galerie existante (hors formulaire principal : chaque action a son propre formulaire) --}}
    @if ($editing && $product->images->isNotEmpty())
        <section class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 xl:max-w-[66%]">
            <h2 class="mb-4 text-lg font-bold">Images actuelles</h2>
            <div class="grid grid-cols-3 gap-4 sm:grid-cols-4 lg:grid-cols-6">
                @foreach ($product->images as $image)
                    <div class="group relative overflow-hidden rounded-xl border {{ $image->is_primary ? 'border-primary-500 ring-2 ring-primary-200' : 'border-gray-100' }}">
                        <img src="{{ $image->url() }}" alt="" class="aspect-square w-full object-cover">
                        @if ($image->is_primary)
                            <span class="absolute left-1.5 top-1.5 rounded-full bg-primary-600 px-2 py-0.5 text-[10px] font-bold text-white">Principale</span>
                        @endif
                        <div class="absolute inset-x-0 bottom-0 flex justify-center gap-1 bg-gray-950/70 p-1.5 opacity-0 transition group-hover:opacity-100">
                            @unless ($image->is_primary)
                                <form action="{{ route('admin.products.images.primary', [$product, $image]) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="rounded-full bg-white/90 px-2 py-1 text-[10px] font-bold text-gray-700 hover:bg-white">Principale</button>
                                </form>
                            @endunless
                            <x-confirm-form :action="route('admin.products.images.destroy', [$product, $image])" message="Supprimer cette image du produit ?">
                                <button type="submit" class="rounded-full bg-danger-500/90 px-2 py-1 text-[10px] font-bold text-white hover:bg-danger-500">✕</button>
                            </x-confirm-form>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

</x-admin-layout>
