@if($paginator->hasPages())
    <div class="janan-pagination" role="group" aria-label="صفحه‌بندی">
        <div class="janan-pagination__summary"><strong>صفحه {{ \App\Support\PersianNumber::digits($paginator->currentPage()) }}</strong></div>
        <div class="store-pagination__inner janan-pagination__controls">
            @if($paginator->onFirstPage())
                <span class="janan-pagination__button is-disabled" aria-disabled="true">قبلی</span>
            @else
                <a class="janan-pagination__button" href="{{ $paginator->previousPageUrl() }}" rel="prev">قبلی</a>
            @endif
            @if($paginator->hasMorePages())
                <a class="janan-pagination__button" href="{{ $paginator->nextPageUrl() }}" rel="next">بعدی</a>
            @else
                <span class="janan-pagination__button is-disabled" aria-disabled="true">بعدی</span>
            @endif
        </div>
    </div>
@endif
