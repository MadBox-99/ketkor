<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MaintenanceReminderStage;
use App\Enums\MaintenanceReminderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Fillable([
    'product_id',
    'user_id',
    'email',
    'stage',
    'stage_key',
    'due_date',
    'last_maintenance_at',
    'sent_at',
    'status',
    'error',
])]
class MaintenanceReminder extends Model
{
    #[Override]
    protected function casts(): array
    {
        return [
            'stage' => MaintenanceReminderStage::class,
            'status' => MaintenanceReminderStatus::class,
            'stage_key' => 'integer',
            'due_date' => 'immutable_date',
            'last_maintenance_at' => 'immutable_date',
            'sent_at' => 'immutable_datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
