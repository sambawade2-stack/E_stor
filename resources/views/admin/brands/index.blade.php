<x-admin-layout title="Marques">

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Création --}}
        <section class="h-fit rounded-2xl border border-gray-100 bg-white p-6">
            <h2 class="mb-4 font-bold">Nouvelle marque</h2>
            <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Nom *</label>
                    <input type="text" id="name" name="name" required value="{{ old('name') }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Logo</label>
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/svg+xml"
                           class="w-full text-sm text-gray-500 file:mr-3 file:rounded-full file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-600 hover:file:bg-primary-100">
                    @error('logo')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-center gap-2.5 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    Marque active
                </label>
                <button type="submit" class="w-full rounded-full bg-primary-600 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700">
                    Créer la marque
                </button>
            </form>
        </section>

        {{-- Liste --}}
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50/70 text-left text-xs uppercase tracking-wide text-gray-400">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Marque</th>
                            <th class="px-6 py-3 font-semibold">Produits</th>
                            <th class="px-6 py-3 font-semibold">Statut</th>
                            <th class="px-6 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($brands as $brand)
                            <tr class="transition hover:bg-gray-50/70" x-data="{ editing: false }">
                                <td class="px-6 py-3" colspan="4" x-show="editing" x-cloak>
                                    <form action="{{ route('admin.brands.update', $brand) }}" method="POST" enctype="multipart/form-data"
                                          class="flex flex-wrap items-center gap-3">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" value="{{ $brand->name }}" required
                                               class="w-44 rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                                        <input type="file" name="logo" accept="image/*" class="text-xs text-gray-500">
                                        <label class="flex items-center gap-2 text-xs">
                                            <input type="hidden" name="is_active" value="0">
                                            <input type="checkbox" name="is_active" value="1" @checked($brand->is_active) class="rounded border-gray-300 text-primary-600">
                                            Active
                                        </label>
                                        <button type="submit" class="rounded-full bg-primary-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-primary-700">Enregistrer</button>
                                        <button type="button" @click="editing = false" class="text-xs text-gray-400 hover:text-gray-600">Annuler</button>
                                    </form>
                                </td>
                                <td class="px-6 py-3" x-show="!editing">
                                    <div class="flex items-center gap-3">
                                        @if ($brand->logo)
                                            <img src="{{ Storage::url($brand->logo) }}" alt="" class="h-9 w-9 rounded-lg border border-gray-100 object-contain">
                                        @else
                                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-gray-100 text-xs font-bold text-gray-400">{{ strtoupper(mb_substr($brand->name, 0, 2)) }}</span>
                                        @endif
                                        <span class="font-semibold">{{ $brand->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-gray-500" x-show="!editing">{{ $brand->products_count }}</td>
                                <td class="px-6 py-3" x-show="!editing">
                                    @if ($brand->is_active)
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-600">Active</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-500">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right" x-show="!editing">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button" @click="editing = true" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-primary-600 transition hover:bg-primary-50">Modifier</button>
                                        <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST"
                                              onsubmit="return confirm('Supprimer cette marque ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-500 transition hover:bg-red-50">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-gray-400">Aucune marque.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-admin-layout>
