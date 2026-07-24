<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tools;

use App\Filament\Resources\Tools\Pages\CreateTool;
use App\Filament\Resources\Tools\Pages\EditTool;
use App\Filament\Resources\Tools\Pages\ListTools;
use App\Filament\Resources\Tools\Schemas\ToolFormSchema;
use App\Filament\Resources\Tools\Tables\ToolTable;
use App\Models\Tool;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class ToolResource extends Resource
{
    protected static ?string $model = Tool::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|UnitEnum|null $navigationGroup = 'Törzsadatok';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Eszközök';

    protected static ?string $modelLabel = 'Eszköz';

    protected static ?string $pluralModelLabel = 'Eszközök';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return ToolFormSchema::make($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return ToolTable::make($table);
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
            'index' => ListTools::route('/'),
            'create' => CreateTool::route('/create'),
            'edit' => EditTool::route('/{record}/edit'),
        ];
    }
}
