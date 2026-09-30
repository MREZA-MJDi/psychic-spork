<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntegrationMapping;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\View\View;

class AdminNilaController extends Controller
{
    public function index(): View
    {
        $productMappings = IntegrationMapping::query()
            ->where('integration', 'nila')
            ->where('entity_type', Product::class)
            ->count();

        $variantMappings = IntegrationMapping::query()
            ->where('integration', 'nila')
            ->where('entity_type', ProductVariant::class)
            ->count();

        $lastMapping = IntegrationMapping::query()
            ->where('integration', 'nila')
            ->latest('updated_at')
            ->first();

        $recentMappings = IntegrationMapping::query()
            ->where('integration', 'nila')
            ->with('entity')
            ->latest('updated_at')
            ->limit(12)
            ->get();

        return view('admin.nila.index', [
            'productMappings' => $productMappings,
            'variantMappings' => $variantMappings,
            'lastMapping' => $lastMapping,
            'recentMappings' => $recentMappings,
        ]);
    }
}
