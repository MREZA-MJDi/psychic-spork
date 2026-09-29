<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WholesaleProfile extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending',
        'approved',
        'suspended',
        'rejected',
    ];

    protected $fillable = [
        'user_id',
        'status',
        'approved_by',
        'approved_at',
        'suspended_by',
        'suspended_at',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'suspended_at' => 'datetime',
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

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
