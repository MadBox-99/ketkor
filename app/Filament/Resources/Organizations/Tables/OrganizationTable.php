<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrganizationTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Név')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('city')
                    ->label('Város')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('tax_number')
                    ->label('Adószám')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('address')
                    ->label('Cím')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('zip')
                    ->label('Irányítószám')
                    ->sortable()
                    ->searchable(),
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
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
