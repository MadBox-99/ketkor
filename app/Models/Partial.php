<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Partial extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'product_id', 'name', 'phone', 'email'];

    public function product(): HasOne
    {
        return $this->hasOne(Product::class);
    }
}