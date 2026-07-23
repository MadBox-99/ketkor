<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductLogType;
use App\Support\MaintenanceSchedule;
use Carbon\CarbonImmutable;
use Database\Factories\ProductFactory;
use DateTime;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\DateFormat;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Override;

#[DateFormat('Y-m-d')]
#[Fillable([
    'owner_name',
    'installer_name',
    'city',
    'street',
    'zip',
    'purchase_place',
    'serial_number',
    'purchase_date',
    'installation_date',
    'warrantee_date',
    'tool_id',
    'user_id',
    'comments',
    'maintenance_interval_months',
    'maintenance_reminders_enabled',
    'created_at',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    #[Override]
    protected function casts(): array
    {
        return [
            'warrantee_date' => 'date:Y-m-d',
            'purchase_date' => 'date:Y-m-d',
            'installation_date' => 'date:Y-m-d',
            'maintenance_interval_months' => 'integer',
            'maintenance_reminders_enabled' => 'boolean',
        ];
    }

    #[Override]
    public function serializeDate($date): string
    {
        if (is_null($date)) {
            $date = new DateTime();
        } elseif (! $date instanceof DateTimeInterface) {
            $date = new DateTime($date);
        }

        return $date->format('Y-m-d');
    }

    /* public function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d');
    } */

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function product_logs(): HasMany
    {
        return $this->hasMany(ProductLog::class);
    }

    /**
     * A készülék legutolsó karbantartási munkalapja.
     */
    public function lastMaintenanceLog(): HasOne
    {
        return $this->hasOne(ProductLog::class)
            ->where('what', ProductLogType::Maintenance)
            ->latestOfMany('when');
    }

    /**
     * A következő karbantartás esedékessége, vagy null, ha nem számítható.
     */
    public function nextMaintenanceDueDate(): ?CarbonImmutable
    {
        return MaintenanceSchedule::for($this)?->dueDate;
    }

    /**
     * Get all of the comments for the Product
     */
    public function partials(): HasMany
    {
        return $this->hasMany(Partial::class);
    }

    public function organizations(): HasManyThrough
    {
        return $this->hasManyThrough(Organization::class, User::class);
    }
}
