@props(['name'])

@php
    $paths = [
        'phone' => 'M8 3H5a2 2 0 0 0-2 2 16 16 0 0 0 16 16 2 2 0 0 0 2-2v-3l-5-2-2 2a12 12 0 0 1-6-6l2-2-2-5Z',
        'basket' => 'M3 10h18l-2 9H5l-2-9Zm4 0 3-5m7 5-3-5M9 13v3m6-3v3',
        'user' => 'M8 7a4 4 0 1 1 8 0 4 4 0 0 1-8 0ZM4 21v-2a5 5 0 0 1 5-5h6a5 5 0 0 1 5 5v2',
        'user-plus' => 'M6 7a4 4 0 1 1 8 0 4 4 0 0 1-8 0ZM2 21v-2a5 5 0 0 1 5-5h6m5 1v6m-3-3h6',
        'login' => 'M12 4h7v16h-7m0-8H3m5-4 4 4-4 4',
        'bag' => 'M5 7h14l2 14H3L5 7Zm4 1V6a3 3 0 0 1 6 0v2',
        'grid' => 'M3 3h7v7H3zm11 0h7v7h-7zM3 14h7v7H3zm11 0h7v7h-7z',
        'layers' => 'm12 3 9 5-9 5-9-5 9-5ZM3 12l9 5 9-5M3 16l9 5 9-5',
        'truck' => 'M1 5h13v12H1zm13 5h4l4 4v3h-8M4 18a2 2 0 1 0 4 0m8 0a2 2 0 1 0 4 0',
        'return' => 'M9 5 4 10l5 5m-5-5h10a5 5 0 1 1 0 10h-4',
        'help' => 'M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0ZM9 9a3 3 0 1 1 4 3c-1 .6-1 1-1 2m0 3h.01',
        'message' => 'M21 11.5a9 8.5 0 0 1-9 8.5 10 10 0 0 1-4-.8L3 21l1.7-4.6A8 8 0 0 1 3 11.5 9 8.5 0 0 1 21 11.5Z',
        'mail' => 'M3 5h18v14H3zM3 6l9 7 9-7',
        'instagram' => 'M8 3h8a5 5 0 0 1 5 5v8a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5V8a5 5 0 0 1 5-5Zm8 9a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm2-5h.01',
        'telegram' => 'm21 3-5 18-5-6-5 3 1-6-4-2 18-7ZM7 12l14-9m-10 12 10-12',
        'shield' => 'M12 22c5-2 8-6 8-11V5l-8-3-8 3v6c0 5 3 9 8 11Zm-3-10 2 2 4-4',
        'document' => 'M6 2h9l4 4v16H6zM9 10h7m-7 4h7m-7 4h4',
        'orders' => 'M8 3h8v4H8zM8 5H5v16h14V5h-3M8 11h8m-8 4h8m-8 3h5',
    ];
@endphp

<svg class="store-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    <path d="{{ $paths[$name] }}"/>
</svg>
