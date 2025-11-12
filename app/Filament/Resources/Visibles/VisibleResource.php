<?php

declare(strict_types=1);

namespace App\Filament\Resources\Visibles;

use App\Filament\Resources\Visibles\Pages\CreateVisible;
use App\Filament\Resources\Visibles\Pages\EditVisible;
use App\Filament\Resources\Visibles\Pages\ListVisibles;
use App\Filament\Resources\Visibles\Schemas\VisibleFormSchema;
use App\Filament\Resources\Visibles\Tables\VisibleTable;
use App\Models\Visible;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class VisibleResource extends Resource
{
    protected static ?string $model = Visible::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return VisibleFormSchema::make($schema);
    }

    public static function table(Table $table): Table
    {
        return VisibleTable::make($table);
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
            'index' => ListVisibles::route('/'),
            'create' => CreateVisible::route('/create'),
            'edit' => EditVisible::route('/{record}/edit'),
        ];
    }
}
