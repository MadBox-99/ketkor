<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Models\Product;
use App\Services\MaintenanceReminderScheduler;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductTable
{
    public static function make(Table $table): Table
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
                TextColumn::make('next_maintenance_due_date')
                    ->label('Következő esedékesség')
                    ->state(fn (Product $record): ?string => $record->nextMaintenanceDueDate()?->toDateString())
                    ->date(),
                IconColumn::make('maintenance_reminders_enabled')
                    ->label('Emlékeztető')
                    ->boolean(),
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
                    ->importer(ProductImporter::class)
                    ->options([
                        'updateExisting' => true,
                    ]),
                ExportAction::make()
                    ->exporter(ProductExporter::class),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('sendMaintenanceReminder')
                    ->label('Emlékeztető küldése')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->requiresConfirmation()
                    ->modalHeading('Karbantartás emlékeztető küldése')
                    ->modalDescription('A készülékhez rendelt ügyfelek azonnal e-mailt kapnak.')
                    ->action(function (Product $record, MaintenanceReminderScheduler $scheduler): void {
                        $sent = $scheduler->sendManually($record);

                        if ($sent === 0) {
                            Notification::make()
                                ->warning()
                                ->title('Nem ment ki emlékeztető')
                                ->body('A készüléknek nincs értesíthető ügyfele, lejárt vagy hiányzik a garanciája, vagy ki van kapcsolva az emlékeztető.')
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->success()
                            ->title(sprintf('%d emlékeztető elküldve', $sent))
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
