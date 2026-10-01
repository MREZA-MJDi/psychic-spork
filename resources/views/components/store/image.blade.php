@props([
    'src' => null,
    'alt' => '',
    'class' => '',
    'loading' => 'lazy',
    'decoding' => 'async',
    'fetchpriority' => null,
    'fallback' => 'JANAN',
    'fallbackClass' => 'store-media-placeholder',
    'fallbackTag' => 'div',
])

@if($src)
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        @if($class) class="{{ $class }}" @endif
        loading="{{ $loading }}"
        decoding="{{ $decoding }}"
        @if($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
        data-store-image-fallback="{{ $fallback }}"
        data-store-image-fallback-class="{{ $fallbackClass }}"
        data-store-image-fallback-tag="{{ $fallbackTag }}"
    >
@else
    <{{ $fallbackTag }}
        class="{{ $fallbackClass }}"
        aria-hidden="true"
    >
        <span>{{ $fallback }}</span>
    </{{ $fallbackTag }}>
@endif
