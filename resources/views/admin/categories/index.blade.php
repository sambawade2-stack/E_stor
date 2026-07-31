<x-admin-layout title="Catégories">

    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.categories.create') }}"
           class="rounded-full bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700">
            + Nouvelle catégorie
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/70 text-left text-xs uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Catégorie</th>
                        <th class="px-6 py-3 font-semibold">Parent</th>
                        <th class="px-6 py-3 font-semibold">Produits</th>
                        <th class="px-6 py-3 font-semibold">Ordre</th>
                        <th class="px-6 py-3 font-semibold">Statut</th>
                        <th class="px-6 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($categories as $category)
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($category->image)
                                        <img src="{{ Storage::url($category->image) }}" alt="" class="h-10 w-10 rounded-lg border border-gray-100 object-cover">
                                    @else
                                        <span class="grid h-10 w-10 place-items-center rounded-lg bg-gray-100 text-gray-400">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/></svg>
                                        </span>
                                    @endif
                                    <div>
                                        <p class="font-semibold">{{ $category->name }}</p>
                                        <p class="text-xs text-gray-400">/{{ $category->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ $category->parent?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $category->products_count }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $category->sort_order }}</td>
                            <td class="px-6 py-3">
                                @if ($category->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-600">Active</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-500">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-primary-600 transition hover:bg-primary-50">Modifier</a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                                          onsubmit="return confirm('Supprimer cette catégorie ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-500 transition hover:bg-red-50">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Aucune catégorie.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-admin-layout>
