<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaImportBatch extends Model
{
    protected $fillable = [
        'original_name',
        'disk',
        'file_path',
        'status',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'product_groups',
        'created_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'total_rows' => 'integer',
            'valid_rows' => 'integer',
            'invalid_rows' => 'integer',
            'product_groups' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
