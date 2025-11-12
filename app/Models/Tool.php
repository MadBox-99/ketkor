<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'tag',
        'factory_name',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function casts(): array
    {
        return [
            'category' => ProductCategory::class,
        ];
    }
}
