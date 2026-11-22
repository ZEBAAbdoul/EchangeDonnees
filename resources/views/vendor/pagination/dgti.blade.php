@if ($paginator->hasPages())
    <nav class="dg-pagination" role="navigation" aria-label="Pagination">
        <ul>
            {{-- Page précédente --}}
            @if ($paginator->onFirstPage())
                <li class="is-disabled" aria-disabled="true"><span aria-label="Page précédente">&larr;</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Page précédente">&larr;</a></li>
            @endif

            {{-- Numéros de pages --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="is-gap" aria-disabled="true"><span>{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="is-active" aria-current="page"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Page suivante --}}
            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Page suivante">&rarr;</a></li>
            @else
                <li class="is-disabled" aria-disabled="true"><span aria-label="Page suivante">&rarr;</span></li>
            @endif
        </ul>
    </nav>

    @once
        @push('styles')
            <style>
                .dg-pagination ul { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; }
                .dg-pagination a, .dg-pagination span { display: grid; place-items: center; min-width: 42px; height: 42px; padding: 0 12px; font: 600 15px/1 var(--dg-sans); color: var(--dg-brand); background: #fff; border: 1px solid var(--dg-line); border-radius: 8px; text-decoration: none; transition: background .2s, color .2s, border-color .2s; }
                .dg-pagination a:hover { background: var(--dg-sky); border-color: var(--dg-sky); color: #fff; }
                .dg-pagination .is-active span { background: var(--dg-brand); border-color: var(--dg-brand); color: #fff; }
                .dg-pagination .is-disabled span, .dg-pagination .is-gap span { color: #9aa7ba; background: transparent; }
                .dg-pagination .is-gap span { border-color: transparent; }
            </style>
        @endpush
    @endonce
@endif