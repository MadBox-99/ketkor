<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductCategory;
use Database\Factories\ToolFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[Fillable([
    'name',
    'category',
    'tag',
    'factory_name',
])]
class Tool extends Model
{
    /** @use HasFactory<ToolFactory> */
    use HasFactory;

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    #[Override]
    public function casts(): array
    {
        return [
            'category' => ProductCategory::class,
        ];
    }
}
