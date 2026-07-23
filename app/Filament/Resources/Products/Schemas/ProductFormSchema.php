<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductFormSchema
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('owner_name')
                    ->maxLength(200),
                TextInput::make('installer_name')
                    ->maxLength(200),
                Select::make('user_id')
                    ->multiple()
                    ->preload()
                    ->relationship('users', 'name'),
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
                    ->preload()
                    ->relationship('tool', 'name')
                    ->required(),
                Select::make('maintenance_interval_months')
                    ->label('Karbantartási ciklus')
                    ->options([
                        6 => 'Féléves',
                        12 => 'Éves',
                    ])
                    ->default(12)
                    ->required(),
                Toggle::make('maintenance_reminders_enabled')
                    ->label('Karbantartás emlékeztető küldése')
                    ->default(true),
            ]);
    }
}
