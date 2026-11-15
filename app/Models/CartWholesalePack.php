<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartWholesalePack extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'wholesale_pack_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function pack(): BelongsTo
    {
        return $this->belongsTo(WholesalePack::class, 'wholesale_pack_id');
    }
}
