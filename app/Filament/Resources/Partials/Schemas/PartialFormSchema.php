<?php

declare(strict_types=1);

namespace App\Filament\Resources\Partials\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PartialFormSchema
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Telefon')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('name')
                    ->label('Név')
                    ->maxLength(255),
                Select::make('product_id')
                    ->label('Termék')
                    ->relationship('product', 'id')
                    ->required(),
            ]);
    }
}
