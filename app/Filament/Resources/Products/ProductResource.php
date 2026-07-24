<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Schemas\ProductFormSchema;
use App\Filament\Resources\Products\Tables\ProductTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    protected static string|UnitEnum|null $navigationGroup = 'Törzsadatok';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Termékek';

    protected static ?string $modelLabel = 'Termék';

    protected static ?string $pluralModelLabel = 'Termékek';

    public static function getNavigationBadge(): string
    {
        return (string) Product::query()->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'primary';
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return ProductFormSchema::make($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return ProductTable::make($table);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
