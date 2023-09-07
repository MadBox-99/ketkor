<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'user_id',
        'what',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}