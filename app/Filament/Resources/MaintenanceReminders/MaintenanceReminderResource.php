<?php

declare(strict_types=1);

namespace App\Filament\Resources\MaintenanceReminders;

use App\Filament\Resources\MaintenanceReminders\Pages\ListMaintenanceReminders;
use App\Filament\Resources\MaintenanceReminders\Tables\MaintenanceReminderTable;
use App\Models\MaintenanceReminder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class MaintenanceReminderResource extends Resource
{
    protected static ?string $model = MaintenanceReminder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?string $navigationLabel = 'Karbantartás emlékeztetők';

    protected static ?string $modelLabel = 'Karbantartás emlékeztető';

    protected static ?string $pluralModelLabel = 'Karbantartás emlékeztetők';

    #[Override]
    public static function table(Table $table): Table
    {
        return MaintenanceReminderTable::make($table);
    }

    #[Override]
    public static function canCreate(): bool
    {
        return false;
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListMaintenanceReminders::route('/'),
        ];
    }
}
