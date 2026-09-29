<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChequePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'enabled',
        'max_order_amount',
        'approved_by',
        'approved_at',
        'disabled_by',
        'disabled_at',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'max_order_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'disabled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function disabledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disabled_by');
    }

    public function allows(float $amount): bool
    {
        return $this->enabled
            && (
                $this->max_order_amount === null
                || $amount <= (float) $this->max_order_amount
            );
    }
}
