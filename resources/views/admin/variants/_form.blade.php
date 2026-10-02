@php
    $variant = $variant ?? new \App\Models\ProductVariant();

    $isEdit = $variant->exists;

    $currentStock = (int) old(
        'stock',
        $variant->stock ?? 0
    );

    $lowStockThreshold = old(
        'low_stock_threshold',
        $variant->low_stock_threshold ?? 5
    );

    $sortOrder = old(
        'sort_order',
        $variant->sort_order ?? 0
    );

    $colorCode = old(
        'color_code',
        $variant->color_code ?? '#000000'
    );

    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $colorCode)) {
        $colorCode = '#000000';
    }
@endphp


