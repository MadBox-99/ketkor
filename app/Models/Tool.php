<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}