<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\RedirectResponse;

class StoreBrandController extends Controller
{
    /**
     * Brands are catalog filters, not standalone storefront destinations.
     * Preserve old links with a permanent redirect to the main catalog.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('products.index', [], 301);
    }

    /**
     * Existing brand links lead directly to the corresponding catalog filter.
     */
    public function show(Brand $brand): RedirectResponse
    {
        abort_unless($brand->is_active, 404);

        return redirect()->route('products.index', [
            'brand' => $brand->slug,
        ], 301);
    }
}