<?php

namespace App\Filament\Resources;

use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('owner_name')
                    ->maxLength(200),
                TextInput::make('installer_name')
                    ->maxLength(200),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('city')
                    ->maxLength(200),
                TextInput::make('street')
                    ->maxLength(200),
                TextInput::make('zip')
                    ->maxLength(4),
                TextInput::make('purchase_place')
                    ->maxLength(200),
                TextInput::make('serial_number')
                    ->required()
                    ->maxLength(200),
                TextInput::make('comments')
                    ->maxLength(500),
                DatePicker::make('installation_date'),
                DatePicker::make('warrantee_date'),
                DatePicker::make('purchase_date'),
                Select::make('tool_id')
                    ->relationship('tool', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('owner_name')
                    ->searchable(),
                TextColumn::make('installer_name')
                    ->searchable(),
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('street')
                    ->searchable(),
                TextColumn::make('zip')
                    ->searchable(),
                TextColumn::make('purchase_place')
                    ->searchable(),
                TextColumn::make('serial_number')
                    ->searchable(),
                TextColumn::make('comments')
                    ->searchable(),
                TextColumn::make('installation_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('warrantee_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('tool.name')
                    ->numeric()
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
            ->headerActions([
                ImportAction::make()
                    ->importer(ProductImporter::class),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
