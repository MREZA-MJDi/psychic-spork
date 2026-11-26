@props(['name'])

<svg class="store-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @if($name === 'phone')
        <path d="M8 3H5a2 2 0 0 0-2 2 16 16 0 0 0 16 16 2 2 0 0 0 2-2v-3l-5-2-2 2a12 12 0 0 1-6-6l2-2-2-5Z"/>
    @else
        <path d="M3 10h18l-2 9H5l-2-9Zm4 0 3-5m7 5-3-5M9 13v3m6-3v3"/>
    @endif
</svg>
