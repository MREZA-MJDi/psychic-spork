@props([
    'eyebrow',
    'title',
    'description',
    'count' => 0,
    'backUrl',
    'backLabel' => 'بازگشت',
    'primaryUrl',
    'primaryLabel' => 'دیدن محصولات',
    'secondaryUrl' => null,
    'secondaryLabel' => null,
    'products',
    'fallbackImage' => null,
    'mark' => null,
    'logo' => null,
])

@php
    $tiles = $products->getCollection()
        ->filter(fn ($product) => filled($product->primaryGalleryMedia?->url))
        ->take(3)
        ->values();
@endphp

<section class="page-hero page-hero--premium page-hero--editorial">
    <div class="container editorial-collection-hero">
        <div class="editorial-collection-hero__copy">
            <a class="page-kicker" href="{{ $backUrl }}">↖ {{ $backLabel }}</a>
            <span class="eyebrow">{{ $eyebrow }}</span>
            <h1>{{ $title }}</h1>
            <p>{{ $description }}</p>

            <div class="editorial-collection-hero__meta">
                <strong>{{ number_format($count) }}</strong>
                <span>محصول فعال</span>
                <i aria-hidden="true"></i>
                <span>JANAN SELECT</span>
            </div>

            <div class="editorial-collection-hero__actions">
                <a class="button button--primary" href="{{ $primaryUrl }}">
                    {{ $primaryLabel }} <span aria-hidden="true">↓</span>
                </a>
                @if($secondaryUrl && $secondaryLabel)
                    <a class="button button--ghost" href="{{ $secondaryUrl }}">{{ $secondaryLabel }}</a>
                @endif
            </div>
        </div>

        <div class="editorial-collection-hero__art {{ $tiles->count() > 1 ? 'has-collage' : '' }}" aria-label="تصاویر محصولات این کالکشن">
            @if($tiles->isNotEmpty())
                @foreach($tiles as $tile)
                    <a
                        class="editorial-collection-hero__tile editorial-collection-hero__tile--{{ $loop->iteration }}"
                        href="{{ route('products.show', $tile) }}"
                        aria-label="مشاهده {{ $tile->name }}"
                    >
                        <x-store.image
                            :src="$tile->primaryGalleryMedia?->url"
                            :alt="$tile->name"
                            :loading="$loop->first ? 'eager' : 'lazy'"
                            :fetchpriority="$loop->first ? 'high' : 'auto'"
                            fallback="JANAN"
                        />
                    </a>
                @endforeach
            @elseif($fallbackImage)
                <div class="editorial-collection-hero__tile editorial-collection-hero__tile--fallback">
                    <x-store.image :src="$fallbackImage" :alt="$title" loading="eager" fetchpriority="high" fallback="JANAN" />
                </div>
            @else
                <div class="editorial-collection-hero__empty" aria-hidden="true">{{ $mark ?: 'J' }}</div>
            @endif

            @if($logo)
                <span class="editorial-collection-hero__logo" aria-label="لوگوی {{ $title }}">
                    <x-store.image :src="$logo" :alt="'لوگوی ' . $title" loading="lazy" fallback="JANAN" />
                </span>
            @endif
            <span class="editorial-collection-hero__stamp" aria-hidden="true">JANAN / EDITION</span>
        </div>
    </div>
</section>
