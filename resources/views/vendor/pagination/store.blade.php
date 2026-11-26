@if ($paginator->hasPages())
    <div class="janan-pagination" role="group" aria-label="صفحه‌بندی">
        <div class="janan-pagination__summary">
            <strong>صفحه {{ \App\Support\PersianNumber::digits($paginator->currentPage()) }} از {{ \App\Support\PersianNumber::digits($paginator->lastPage()) }}</strong>
            @if($paginator->firstItem() !== null)
                <small>نمایش {{ \App\Support\PersianNumber::format($paginator->firstItem()) }} تا {{ \App\Support\PersianNumber::format($paginator->lastItem()) }} از {{ \App\Support\PersianNumber::format($paginator->total()) }} مورد</small>
            @endif
        </div>
        <div class="store-pagination__inner janan-pagination__controls">
            @if ($paginator->onFirstPage())
                <span class="janan-pagination__button janan-pagination__direction is-disabled" aria-disabled="true">قبلی</span>
            @else
                <a class="janan-pagination__button janan-pagination__direction" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="صفحه قبلی">قبلی</a>
            @endif
            <div class="janan-pagination__numbers">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="janan-pagination__dots" aria-hidden="true">…</span>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="janan-pagination__button is-current" aria-current="page" aria-label="صفحه {{ \App\Support\PersianNumber::digits($page) }}">{{ \App\Support\PersianNumber::digits($page) }}</span>
                            @else
                                <a class="janan-pagination__button" href="{{ $url }}" aria-label="رفتن به صفحه {{ \App\Support\PersianNumber::digits($page) }}">{{ \App\Support\PersianNumber::digits($page) }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>
            @if ($paginator->hasMorePages())
                <a class="janan-pagination__button janan-pagination__direction" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="صفحه بعدی">بعدی</a>
            @else
                <span class="janan-pagination__button janan-pagination__direction is-disabled" aria-disabled="true">بعدی</span>
            @endif
        </div>
    </div>
@endif
