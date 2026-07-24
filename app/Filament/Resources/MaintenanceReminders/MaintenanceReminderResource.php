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
use Illuminate\Database\Eloquent\Model;
use Override;
use UnitEnum;

final class MaintenanceReminderResource extends Resource
{
    protected static ?string $model = MaintenanceReminder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|UnitEnum|null $navigationGroup = 'Karbantartás';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Karbantartás emlékeztetők';

    protected static ?string $modelLabel = 'Karbantartás emlékeztető';

    protected static ?string $pluralModelLabel = 'Karbantartás emlékeztetők';

    public static function getNavigationBadge(): string
    {
        return (string) MaintenanceReminder::query()->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

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
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    #[Override]
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    #[Override]
    public static function canDeleteAny(): bool
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
