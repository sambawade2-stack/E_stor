@php $editing = $category->exists; @endphp

<x-admin-layout :title="$editing ? 'Modifier : '.$category->name : 'Nouvelle catégorie'">

    <form action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
          method="POST" enctype="multipart/form-data" class="max-w-2xl space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <section class="rounded-2xl border border-gray-100 bg-white p-6">
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Nom *</label>
                    <input type="text" id="name" name="name" required value="{{ old('name', $category->name) }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('description', $category->description) }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="parent_id" class="mb-1.5 block text-sm font-medium text-gray-700">Catégorie parente</label>
                    <select id="parent_id" name="parent_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Aucune (racine)</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                    @error('parent_id')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="sort_order" class="mb-1.5 block text-sm font-medium text-gray-700">Ordre d'affichage</label>
                    <input type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                           class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Image</label>
                    @if ($category->image)
                        <img src="{{ Storage::disk('public')->url($category->image) }}" alt="" class="mb-3 h-20 w-20 rounded-xl border border-gray-100 object-cover">
                    @endif
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                           class="w-full text-sm text-gray-500 file:mr-3 file:rounded-full file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-600 hover:file:bg-primary-100">
                    @error('image')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-center gap-2.5 text-sm sm:col-span-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    Catégorie active
                </label>
            </div>
        </section>

        <div class="flex gap-2">
            <button type="submit" class="rounded-full bg-primary-600 px-7 py-3 text-sm font-semibold text-white transition hover:bg-primary-700">
                {{ $editing ? 'Enregistrer' : 'Créer la catégorie' }}
            </button>
            <a href="{{ route('admin.categories.index') }}" class="rounded-full border border-gray-200 px-6 py-3 text-sm font-medium text-gray-500 transition hover:bg-gray-50">Annuler</a>
        </div>
    </form>

</x-admin-layout>
