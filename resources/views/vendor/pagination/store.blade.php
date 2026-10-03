@if ($paginator->hasPages())
    <div class="store-pagination__inner">
        @if ($paginator->onFirstPage())
            <span class="store-pagination__direction is-disabled" aria-disabled="true">قبلی</span>
        @else
            <a class="store-pagination__direction" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="صفحه قبلی">قبلی</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="store-pagination__dots" aria-hidden="true">…</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="store-pagination__number is-current" aria-current="page" aria-label="صفحه {{ $page }}">{{ $page }}</span>
                    @else
                        <a class="store-pagination__number" href="{{ $url }}" aria-label="رفتن به صفحه {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="store-pagination__direction" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="صفحه بعدی">بعدی</a>
        @else
            <span class="store-pagination__direction is-disabled" aria-disabled="true">بعدی</span>
        @endif
    </div>
@endif
