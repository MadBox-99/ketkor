<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Imports\UserImporter;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Név')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('organization.name')
                    ->label('Szervezet')
                    ->sortable()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->label('E-mail megerősítve')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Szerepkörök')
                    ->searchable()
                    ->getStateUsing(fn (User $record) => $record->roles->pluck('name')->implode(', ')),
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
            ->headerActions([
                ImportAction::make()
                    ->importer(UserImporter::class),
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
