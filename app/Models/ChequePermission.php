<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChequePermission extends Model
{
    use HasFactory;

    public const STATUS_NONE = 'none';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DISABLED = 'disabled';

    protected $fillable = [
        'user_id',
        'enabled',
        'status',
        'requested_at',
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
            'status' => 'string',
            'requested_at' => 'datetime',
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
            && $this->status === self::STATUS_APPROVED
            && (
                $this->max_order_amount === null
                || $amount <= (float) $this->max_order_amount
            );
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->enabled && $this->status === self::STATUS_APPROVED;
    }
}
