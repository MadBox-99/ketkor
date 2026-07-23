<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Product;
use Carbon\CarbonImmutable;

final readonly class MaintenanceSchedule
{
    public function __construct(
        public CarbonImmutable $baseDate,
        public CarbonImmutable $dueDate,
        public bool $fromMaintenanceLog,
    ) {}

    /**
     * A készülék következő karbantartásának esedékessége, vagy null, ha nincs mihez viszonyítani.
     */
    public static function for(Product $product): ?self
    {
        $log = $product->lastMaintenanceLog;
        $base = $log?->when ?? $product->installation_date;

        if ($base === null) {
            return null;
        }

        $baseDate = CarbonImmutable::parse($base)->startOfDay();

        return new self(
            baseDate: $baseDate,
            dueDate: $baseDate->addMonthsNoOverflow($product->maintenance_interval_months),
            fromMaintenanceLog: $log !== null,
        );
    }
}
