<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id','brand_id','name','slug','short_description','description','attributes',
        'meta_title','meta_description','is_active','is_featured','is_hero','sort_order',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_hero' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->where('is_active', true)
            ->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The single active variant used by catalog cards and lightweight listings.
     *
     * Keep full variants loading for the product detail page only.
     */
    public function primaryActiveVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->ofMany(
            ['sort_order' => 'min', 'id' => 'min'],
            fn ($query) => $query->where('is_active', true)
        );
    }

    public function orderItems(): HasMany { return $this->hasMany(OrderItem::class); }
    public function wishlistItems(): HasMany { return $this->hasMany(WishlistItem::class); }
    public function media(): MorphMany { return $this->morphMany(Media::class, 'mediable'); }

    public function galleryMedia(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')
            ->where('collection', 'gallery')
            ->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The first gallery asset used by lightweight product listings.
     */
    public function primaryGalleryMedia(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable')->ofMany(
            ['sort_order' => 'min', 'id' => 'min'],
            fn ($query) => $query
                ->where('collection', 'gallery')
                ->whereNotNull('path')
                ->whereRaw("TRIM(path) <> ''")
        );
    }

    public function integrationMappings(): MorphMany
    {
        return $this->morphMany(IntegrationMapping::class, 'entity');
    }

    public function defaultVariant(): ?ProductVariant
    {
        return $this->relationLoaded('variants')
            ? $this->variants->first()
            : $this->variants()->first();
    }

    public function scopeOrderByEffectivePrice($query, string $direction = 'asc')
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        // Storefront price sorting is paired with withCatalogMetrics(), which
        // joins one grouped variant aggregate instead of running a correlated
        // MIN() query once for each product in the result set.
        return $query
            ->orderBy('catalog_variant_metrics.minimum_effective_price', $direction)
            ->orderBy('products.id');
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeFeatured($query) { return $query->where('is_featured', true); }

    public function scopeWithCatalogMetrics($query)
    {
        $metrics = ProductVariant::query()
            ->select('product_id')
            ->selectRaw('COUNT(*) as active_variants_count')
            ->selectRaw("COUNT(DISTINCT NULLIF(TRIM(color), '')) as active_variant_colors_count")
            ->selectRaw("COUNT(DISTINCT NULLIF(TRIM(size), '')) as active_variant_sizes_count")
            ->selectRaw('COALESCE(SUM(stock), 0) as active_stock_total')
            ->selectRaw('MIN(COALESCE(sale_price, price)) as minimum_effective_price')
            ->selectRaw('MAX(COALESCE(sale_price, price)) as maximum_effective_price')
            ->where('is_active', true)
            ->groupBy('product_id');

        return $query
            ->leftJoinSub($metrics, 'catalog_variant_metrics', function ($join): void {
                $join->on(
                    'catalog_variant_metrics.product_id',
                    '=',
                    'products.id'
                );
            })
            ->addSelect([
                DB::raw('COALESCE(catalog_variant_metrics.active_variants_count, 0) as active_variants_count'),
                DB::raw('COALESCE(catalog_variant_metrics.active_variant_colors_count, 0) as active_variant_colors_count'),
                DB::raw('COALESCE(catalog_variant_metrics.active_variant_sizes_count, 0) as active_variant_sizes_count'),
                DB::raw('COALESCE(catalog_variant_metrics.active_stock_total, 0) as active_stock_total'),
                'catalog_variant_metrics.minimum_effective_price',
                'catalog_variant_metrics.maximum_effective_price',
            ]);
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('activeVariants', fn ($q) =>
            $q->whereColumn('stock', '<=', 'low_stock_threshold')
        );
    }
}
