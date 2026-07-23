<?php

declare(strict_types=1);

namespace App\Filament\Resources\MaintenanceReminders\Tables;

use App\Enums\MaintenanceReminderStage;
use App\Enums\MaintenanceReminderStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceReminderTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('product.serial_number')
                    ->label('Gyári szám')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Ügyfél')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail cím')
                    ->searchable(),
                TextColumn::make('stage')
                    ->label('Szakasz')
                    ->badge(),
                TextColumn::make('stage_key')
                    ->label('Sorszám / nap')
                    ->numeric(),
                TextColumn::make('last_maintenance_at')
                    ->label('Előző karbantartás')
                    ->date()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Esedékesség')
                    ->date()
                    ->sortable(),
                TextColumn::make('sent_at')
                    ->label('Kiküldve')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Státusz')
                    ->badge()
                    ->color(fn (MaintenanceReminderStatus $state): string => match ($state) {
                        MaintenanceReminderStatus::Sent => 'success',
                        MaintenanceReminderStatus::Failed => 'danger',
                        MaintenanceReminderStatus::Pending => 'warning',
                    }),
                TextColumn::make('error')
                    ->label('Hibaüzenet')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Státusz')
                    ->options(MaintenanceReminderStatus::class),
                SelectFilter::make('stage')
                    ->label('Szakasz')
                    ->options(MaintenanceReminderStage::class),
                Filter::make('sent_at')
                    ->label('Kiküldés dátuma')
                    ->schema([
                        DatePicker::make('sent_from')
                            ->label('Kiküldve ettől'),
                        DatePicker::make('sent_until')
                            ->label('Kiküldve eddig'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['sent_from'] ?? null,
                            fn (Builder $query, string $date): Builder => $query->whereDate('sent_at', '>=', $date),
                        )
                        ->when(
                            $data['sent_until'] ?? null,
                            fn (Builder $query, string $date): Builder => $query->whereDate('sent_at', '<=', $date),
                        )),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
