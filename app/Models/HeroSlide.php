<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroSlide extends Model
{
    protected $fillable = [
        'product_id',
        'title',
        'description',
        'image_path',
        'link_url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getResolvedImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return preg_match('/^https?:\/\//i', $this->image_path)
                ? $this->image_path
                : route('store.media', ['path' => ltrim($this->image_path, '/')]);
        }

        return $this->product?->primaryGalleryMedia?->url
            ?: $this->product?->brand?->logoMedia?->url;
    }
}
