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
                    ->label('Tulajdonos neve')
                    ->maxLength(200),
                TextInput::make('installer_name')
                    ->label('Beüzemelő neve')
                    ->maxLength(200),
                Select::make('user_id')
                    ->label('Felhasználók')
                    ->multiple()
                    ->preload()
                    ->relationship('users', 'name'),
                TextInput::make('city')
                    ->label('Város')
                    ->maxLength(200),
                TextInput::make('street')
                    ->label('Utca')
                    ->maxLength(200),
                TextInput::make('zip')
                    ->label('Irányítószám')
                    ->maxLength(4),
                TextInput::make('purchase_place')
                    ->label('Vásárlás helye')
                    ->maxLength(200),
                TextInput::make('serial_number')
                    ->label('Gyári szám')
                    ->required()
                    ->maxLength(200),
                TextInput::make('comments')
                    ->label('Megjegyzések')
                    ->maxLength(500),
                DatePicker::make('installation_date')
                    ->label('Beüzemelés dátuma'),
                DatePicker::make('warrantee_date')
                    ->label('Garancia lejárata'),
                DatePicker::make('purchase_date')
                    ->label('Vásárlás dátuma'),
                Select::make('tool_id')
                    ->label('Eszköz')
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
