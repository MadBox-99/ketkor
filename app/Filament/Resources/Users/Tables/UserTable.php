<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Imports\Importer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserTable
{
    public static function make(Table $table, ?string $importerClass = null): Table
    {
        $tableInstance = $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('organization.name')
                    ->sortable()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Szerepkörök')
                    ->searchable()
                    ->getStateUsing(fn (User $record) => $record->roles->pluck('name')->implode(', ')),
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

        if ($importerClass !== null && is_subclass_of($importerClass, Importer::class)) {
            $tableInstance->headerActions([
                ImportAction::make()
                    ->importer($importerClass),
            ]);
        }

        return $tableInstance;
    }
}
