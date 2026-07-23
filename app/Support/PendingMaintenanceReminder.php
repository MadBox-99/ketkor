<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\MaintenanceReminderStage;
use App\Models\Product;
use App\Models\User;

final readonly class PendingMaintenanceReminder
{
    public function __construct(
        public Product $product,
        public User $user,
        public MaintenanceReminderStage $stage,
        public int $stageKey,
        public MaintenanceSchedule $schedule,
    ) {}
}
