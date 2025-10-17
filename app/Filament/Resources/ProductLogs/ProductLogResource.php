<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\ProductLogResource\Pages\ListProductLogs;
use App\Filament\Resources\ProductLogResource\Pages\CreateProductLog;
use App\Filament\Resources\ProductLogResource\Pages\EditProductLog;
use App\Filament\Resources\ProductLogResource\Pages;
use App\Filament\Resources\ProductLogResource\RelationManagers;
use App\Models\ProductLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductLogResource extends Resource
{
    protected static ?string $model = ProductLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            'index' => ListProductLogs::route('/'),
            'create' => CreateProductLog::route('/create'),
            'edit' => EditProductLog::route('/{record}/edit'),
        ];
    }
}
