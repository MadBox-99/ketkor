<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'product_id',
        'what',
        'comment',
        'when',
        'is_online',
        'worksheet_id',
        'signature',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'when' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
