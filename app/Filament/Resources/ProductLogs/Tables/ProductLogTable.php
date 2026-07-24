<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductLogTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.id')
                    ->label('Termék azonosító')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('what')
                    ->label('Művelet')
                    ->searchable(),
                TextColumn::make('comment')
                    ->label('Megjegyzés')
                    ->searchable(),
                TextColumn::make('when')
                    ->label('Időpont')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_online')
                    ->label('Online')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheck)
                    ->falseIcon(Heroicon::OutlinedXMark),
                TextColumn::make('worksheet_id')
                    ->label('Munkalap azonosító')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Létrehozva')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Módosítva')
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
}
