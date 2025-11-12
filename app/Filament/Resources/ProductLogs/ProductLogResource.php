<?php

namespace App\Filament\Resources\ProductLogs;

use App\Filament\Resources\ProductLogs\Pages\CreateProductLog;
use App\Filament\Resources\ProductLogs\Pages\EditProductLog;
use App\Filament\Resources\ProductLogs\Pages\ListProductLogs;
use App\Models\ProductLog;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductLogResource extends Resource
{
    protected static ?string $model = ProductLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'id')
                    ->required(),
                TextInput::make('what')
                    ->maxLength(500),
                TextInput::make('comment')
                    ->maxLength(255),
                DateTimePicker::make('when')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('what')
                    ->searchable(),
                TextColumn::make('comment')
                    ->searchable(),
                TextColumn::make('when')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
