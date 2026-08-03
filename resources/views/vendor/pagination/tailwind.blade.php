@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigation de pagination" class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">

        <p class="text-sm text-gray-500">
            @if ($paginator->firstItem())
                <span class="font-semibold text-gray-900">{{ $paginator->firstItem() }}</span>
                –
                <span class="font-semibold text-gray-900">{{ $paginator->lastItem() }}</span>
                sur
                <span class="font-semibold text-gray-900">{{ $paginator->total() }}</span>
            @else
                {{ $paginator->count() }} résultat{{ $paginator->count() > 1 ? 's' : '' }}
            @endif
        </p>

        <div class="flex items-center gap-1.5">
            {{-- Précédent --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="Précédent"
                      class="grid h-10 w-10 shrink-0 cursor-not-allowed place-items-center rounded-full text-gray-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Page précédente"
                   class="grid h-10 w-10 shrink-0 place-items-center rounded-full text-gray-500 transition duration-200 hover:bg-gray-100 hover:text-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </a>
            @endif

            {{-- Numéros de page --}}
            <div class="flex items-center gap-1.5">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="grid h-10 w-10 shrink-0 place-items-center text-sm text-gray-400" aria-hidden="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-primary-600 to-accent-500 text-sm font-bold text-white shadow-lg shadow-primary-600/30">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" aria-label="Aller à la page {{ $page }}"
                                   class="grid h-10 w-10 shrink-0 place-items-center rounded-full text-sm font-medium text-gray-600 transition duration-200 hover:bg-gray-100 hover:text-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Suivant --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Page suivante"
                   class="grid h-10 w-10 shrink-0 place-items-center rounded-full text-gray-500 transition duration-200 hover:bg-gray-100 hover:text-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </a>
            @else
                <span aria-disabled="true" aria-label="Suivant"
                      class="grid h-10 w-10 shrink-0 cursor-not-allowed place-items-center rounded-full text-gray-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
