<?php

declare(strict_types=1);

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
                    ->label('Cég neve')
                    ->required()
                    ->maxLength(300),
                TextInput::make('city')
                    ->label('Város')
                    ->maxLength(300),
                TextInput::make('tax_number')
                    ->label('Adószám')
                    ->required()
                    ->maxLength(24),
                TextInput::make('address')
                    ->label('Cím')
                    ->maxLength(300),
                TextInput::make('zip')
                    ->label('Irányítószám')
                    ->maxLength(300),
            ]);
    }
}
