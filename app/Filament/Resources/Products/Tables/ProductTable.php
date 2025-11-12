<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\ImportAction;
use Filament\Actions\Imports\Importer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductTable
{
    public static function make(
        Table $table,
        ?string $importerClass = null,
        ?string $exporterClass = null
    ): Table {
        $headerActions = [];

        if ($importerClass !== null && is_subclass_of($importerClass, Importer::class)) {
            $headerActions[] = ImportAction::make()
                ->importer($importerClass)
                ->options([
                    'updateExisting' => true,
                ]);
        }

        if ($exporterClass !== null && is_subclass_of($exporterClass, Exporter::class)) {
            $headerActions[] = ExportAction::make()
                ->exporter($exporterClass);
        }

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
            ->headerActions($headerActions)
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
