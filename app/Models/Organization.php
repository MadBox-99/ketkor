<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'city',
        'address',
        'zip',
        'tax_number',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasManyThrough(Product::class, User::class);
    }
}
