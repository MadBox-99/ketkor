<?php

declare(strict_types=1);

namespace App\Livewire\Products\Support;

use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;

final class MaintenanceWindow
{
    /**
     * @return array{start: CarbonInterface, end: CarbonInterface}
     */
    public static function nextWindow(Product $product): array
    {
        $warranteeDate = Date::parse($product->serializeDate($product->warrantee_date));

        return [
            'start' => $warranteeDate->copy()->subMonth(),
            'end' => $warranteeDate->copy()->addMonths(2),
        ];
    }

    public static function allows(Product $product, CarbonInterface $now): bool
    {
        $window = self::nextWindow($product);

        return $now->between($window['start'], $window['end']);
    }
}
