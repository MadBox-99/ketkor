<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Fillable([
    'id',
    'product_id',
    'what',
    'comment',
    'when',
    'is_online',
    'worksheet_id',
    'signature',
    'created_at',
])]
class ProductLog extends Model
{
    /** @use HasFactory<ProductLogFactory> */
    use HasFactory;

    #[Override]
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
