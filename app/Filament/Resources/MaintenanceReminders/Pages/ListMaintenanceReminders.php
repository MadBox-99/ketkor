<?php

declare(strict_types=1);

namespace App\Filament\Resources\MaintenanceReminders\Pages;

use App\Filament\Resources\MaintenanceReminders\MaintenanceReminderResource;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListMaintenanceReminders extends ListRecords
{
    protected static string $resource = MaintenanceReminderResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [];
    }
}
