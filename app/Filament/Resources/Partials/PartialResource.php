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
use Filament\Tables\Table;

class PartialResource extends Resource
{
    protected static ?string $model = Partial::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return PartialFormSchema::make($schema);
    }

    public static function table(Table $table): Table
    {
        return PartialTable::make($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPartials::route('/'),
            'create' => CreatePartial::route('/create'),
            'edit' => EditPartial::route('/{record}/edit'),
        ];
    }
}
