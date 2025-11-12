<?php

namespace App\Filament\Resources\Organizations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrganizationFormSchema
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Organization name')
                    ->required()
                    ->maxLength(300),
                TextInput::make('city')
                    ->label('City')
                    ->maxLength(300),
                TextInput::make('tax_number')
                    ->label('Tax number')
                    ->maxLength(300),
                TextInput::make('address')
                    ->label('Address')
                    ->maxLength(300),
                TextInput::make('zip')
                    ->label('ZIP code')
                    ->maxLength(300),
            ]);
    }
}
