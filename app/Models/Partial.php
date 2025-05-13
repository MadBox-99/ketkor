<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Partial extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'product_id', 'name', 'phone', 'email'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
