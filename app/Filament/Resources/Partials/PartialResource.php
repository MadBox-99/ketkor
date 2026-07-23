<?php

declare(strict_types=1);

namespace App\Filament\Resources\Partials;

use App\Filament\Resources\Partials\Pages\CreatePartial;
use App\Filament\Resources\Partials\Pages\EditPartial;
use App\Filament\Resources\Partials\Pages\ListPartials;
use App\Filament\Resources\Partials\Schemas\PartialFormSchema;
use App\Filament\Resources\Partials\Tables\PartialTable;
use App\Models\Partial;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class PartialResource extends Resource
{
    protected static ?string $model = Partial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return PartialFormSchema::make($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return PartialTable::make($table);
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
            'index' => ListPartials::route('/'),
            'create' => CreatePartial::route('/create'),
            'edit' => EditPartial::route('/{record}/edit'),
        ];
    }
}
