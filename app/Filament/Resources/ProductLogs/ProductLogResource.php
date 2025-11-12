<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductLogs;

use App\Filament\Resources\ProductLogs\Pages\CreateProductLog;
use App\Filament\Resources\ProductLogs\Pages\EditProductLog;
use App\Filament\Resources\ProductLogs\Pages\ListProductLogs;
use App\Filament\Resources\ProductLogs\Schemas\ProductLogFormSchema;
use App\Filament\Resources\ProductLogs\Tables\ProductLogTable;
use App\Models\ProductLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProductLogResource extends Resource
{
    protected static ?string $model = ProductLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return ProductLogFormSchema::make($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductLogTable::make($table);
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
            'index' => ListProductLogs::route('/'),
            'create' => CreateProductLog::route('/create'),
            'edit' => EditProductLog::route('/{record}/edit'),
        ];
    }
}
