@props(['category' => null, 'onSaleOnly' => false, 'brands' => collect()])

@php
    // Construit l'URL courante en retirant certaines clés de query
    // string (et toujours 'page', puisque le résultat filtré change).
    $without = function (array $keys) {
        $query = request()->except([...$keys, 'page']);

        return $query === [] ? url()->current() : url()->current().'?'.http_build_query($query);
    };

    $chips = collect();

    if ($category) {
        $chips->push(['label' => $category->name, 'url' => route('shop.catalog', request()->except('page'))]);
    }

    if ($onSaleOnly) {
        $chips->push(['label' => 'Promotions', 'url' => route('shop.catalog', request()->except('page'))]);
    }

    if (request()->filled('q')) {
        $chips->push(['label' => '« '.request()->string('q').' »', 'url' => $without(['q'])]);
    }

    if (request()->filled('brand')) {
        $brandLabel = $brands->firstWhere('slug', request('brand'))?->name ?? request('brand');
        $chips->push(['label' => $brandLabel, 'url' => $without(['brand'])]);
    }

    if (request()->filled('min_price') || request()->filled('max_price')) {
        $min = request('min_price');
        $max = request('max_price');
        $label = match (true) {
            $min && $max => format_price($min).' – '.format_price($max),
            (bool) $min => 'À partir de '.format_price($min),
            default => 'Jusqu\'à '.format_price($max),
        };
        $chips->push(['label' => $label, 'url' => $without(['min_price', 'max_price'])]);
    }

    if (request()->boolean('in_stock')) {
        $chips->push(['label' => 'En stock uniquement', 'url' => $without(['in_stock'])]);
    }
@endphp

@if ($chips->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }}>
        <span class="text-xs font-medium uppercase tracking-wide text-gray-400">Filtres :</span>

        @foreach ($chips as $chip)
            <a href="{{ $chip['url'] }}"
               class="group inline-flex items-center gap-1.5 rounded-full border border-primary-100 bg-primary-50 py-1.5 pl-3.5 pr-2 text-xs font-semibold text-primary-700 transition hover:border-primary-200 hover:bg-primary-100">
                {{ $chip['label'] }}
                <span class="grid h-4 w-4 place-items-center rounded-full text-primary-400 transition group-hover:bg-primary-600 group-hover:text-white">
                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </span>
            </a>
        @endforeach

        @if ($chips->count() > 1)
            <a href="{{ $category ? route('shop.category', $category) : ($onSaleOnly ? route('shop.promotions') : route('shop.catalog')) }}"
               class="text-xs font-semibold text-gray-400 underline decoration-dotted underline-offset-2 transition hover:text-gray-600">
                Tout effacer
            </a>
        @endif
    </div>
@endif
