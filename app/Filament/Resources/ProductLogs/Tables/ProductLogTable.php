<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                    ->numeric()
                    ->sortable(),
                TextColumn::make('what')
                    ->searchable(),
                TextColumn::make('comment')
                    ->searchable(),
                TextColumn::make('when')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_online')
                    ->label('Online')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark'),
                IconColumn::make('signature')
                    ->label('Signature')
                    ->boolean()
                    ->icon(fn ($state): ?string => $state ? 'heroicon-o-check-circle' : null)
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
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
}
