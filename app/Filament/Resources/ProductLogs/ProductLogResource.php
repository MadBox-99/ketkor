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
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class ProductLogResource extends Resource
{
    protected static ?string $model = ProductLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return ProductLogFormSchema::make($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return ProductLogTable::make($table);
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
            'index' => ListProductLogs::route('/'),
            'create' => CreateProductLog::route('/create'),
            'edit' => EditProductLog::route('/{record}/edit'),
        ];
    }
}
