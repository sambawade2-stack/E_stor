<x-admin-layout title="Avis clients">

    <div class="mb-6 flex items-center gap-2">
        <a href="{{ route('admin.reviews.index') }}"
           class="rounded-full px-4 py-2 text-sm font-semibold {{ request('state') !== 'pending' ? 'bg-gray-900 text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Tous
        </a>
        <a href="{{ route('admin.reviews.index', ['state' => 'pending']) }}"
           class="rounded-full px-4 py-2 text-sm font-semibold {{ request('state') === 'pending' ? 'bg-gray-900 text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            En attente ({{ $pendingCount }})
        </a>
    </div>

    <div class="space-y-4">
        @forelse ($reviews as $review)
            <article class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-gray-100 bg-white p-5">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="font-semibold">{{ $review->author_name }}</span>
                        <x-rating-stars :rating="$review->rating" />
                        @if ($review->is_approved)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-600">Approuvé</span>
                        @else
                            <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-600">En attente</span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        Sur <a href="{{ route('shop.product', $review->product) }}" target="_blank" class="font-medium text-primary-600 hover:underline">{{ $review->product->name }}</a>
                        · {{ $review->created_at->format('d/m/Y') }}
                    </p>
                    @if ($review->comment)
                        <p class="mt-2 text-sm text-gray-600">{{ $review->comment }}</p>
                    @endif
                </div>
                <div class="flex shrink-0 gap-2">
                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="rounded-full px-4 py-2 text-xs font-semibold transition {{ $review->is_approved ? 'border border-gray-200 text-gray-500 hover:bg-gray-50' : 'bg-emerald-500 text-white hover:bg-emerald-600' }}">
                            {{ $review->is_approved ? 'Masquer' : 'Approuver' }}
                        </button>
                    </form>
                    <x-confirm-form :action="route('admin.reviews.destroy', $review)" message="Supprimer cet avis de {{ $review->author_name }} ? Cette action est irréversible.">
                        <button type="submit" class="rounded-full border border-danger-200 px-4 py-2 text-xs font-semibold text-danger-500 transition hover:bg-danger-50">Supprimer</button>
                    </x-confirm-form>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-200 bg-white py-16 text-center text-gray-400">Aucun avis.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $reviews->links() }}</div>

</x-admin-layout>
