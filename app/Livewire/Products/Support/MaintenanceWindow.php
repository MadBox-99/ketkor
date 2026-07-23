<?php

declare(strict_types=1);

namespace App\Livewire\Products\Support;

use Carbon\CarbonInterface;

/**
 * The 11-13 month guaranteed maintenance window measured from a reference
 * date (e.g. a commissioning or previous maintenance log's timestamp).
 */
final readonly class MaintenanceWindow
{
    private const int WINDOW_OPENS_AFTER_MONTHS = 11;

    private const int WINDOW_CLOSES_AFTER_MONTHS = 13;

    private CarbonInterface $start;

    private CarbonInterface $end;

    public function __construct(CarbonInterface $referenceDate)
    {
        $this->start = $referenceDate->copy()->addMonths(self::WINDOW_OPENS_AFTER_MONTHS);
        $this->end = $referenceDate->copy()->addMonths(self::WINDOW_CLOSES_AFTER_MONTHS);
    }

    public function start(): CarbonInterface
    {
        return $this->start;
    }

    public function end(): CarbonInterface
    {
        return $this->end;
    }

    /**
     * Whether the given moment falls within the window, inclusive of both bounds.
     */
    public function contains(CarbonInterface $moment): bool
    {
        return $moment->between($this->start, $this->end);
    }

    /**
     * Whether the given moment is strictly before the window opens.
     */
    public function isBeforeWindow(CarbonInterface $moment): bool
    {
        return $moment->lessThan($this->start);
    }

    /**
     * Whether the given moment is strictly after the window closes.
     */
    public function isAfterWindow(CarbonInterface $moment): bool
    {
        return $moment->greaterThan($this->end);
    }
}
