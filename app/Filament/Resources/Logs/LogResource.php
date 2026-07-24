<?php

declare(strict_types=1);

namespace App\Filament\Resources\Logs;

use App\Filament\Resources\Logs\Pages\CreateLog;
use App\Filament\Resources\Logs\Pages\EditLog;
use App\Filament\Resources\Logs\Pages\ListLogs;
use App\Filament\Resources\Logs\Schemas\LogFormSchema;
use App\Filament\Resources\Logs\Tables\LogTable;
use App\Models\Log;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class LogResource extends Resource
{
    protected static ?string $model = Log::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static string|UnitEnum|null $navigationGroup = 'Előzmények';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Előzmények';

    protected static ?string $modelLabel = 'Előzmény';

    protected static ?string $pluralModelLabel = 'Előzmények';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return LogFormSchema::make($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return LogTable::make($table);
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListLogs::route('/'),
            'create' => CreateLog::route('/create'),
            'edit' => EditLog::route('/{record}/edit'),
        ];
    }
}
