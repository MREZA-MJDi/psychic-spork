<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderWholesalePack extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'wholesale_pack_id',
        'pack_name',
        'pack_slug',
        'image_path',
        'quantity',
        'unit_pack_price',
        'line_total',
        'items_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_pack_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'items_snapshot' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function pack(): BelongsTo
    {
        return $this->belongsTo(WholesalePack::class, 'wholesale_pack_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'wholesale_pack_order_id');
    }
}
