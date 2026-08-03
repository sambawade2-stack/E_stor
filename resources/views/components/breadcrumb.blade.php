@props(['items'])
{{--
    $items : tableau ordonné ['label' => 'Accueil', 'url' => route(...)] ;
    le dernier élément peut omettre 'url' pour représenter la page courante.
--}}

<nav aria-label="Fil d'Ariane" class="mb-2">
    <ol class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400">
        @foreach ($items as $index => $item)
            <li class="flex items-center gap-1.5">
                @if ($index > 0)
                    <svg class="h-3 w-3 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                @endif

                @if (! empty($item['url']) && ! $loop->last)
                    <a href="{{ $item['url'] }}" class="line-clamp-1 max-w-[12rem] rounded transition hover:text-primary-600 focus-visible:outline-none">{{ $item['label'] }}</a>
                @else
                    <span class="line-clamp-1 max-w-[16rem] font-medium text-gray-600" aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
