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
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('name')
                    ->maxLength(255),
                Select::make('product_id')
                    ->relationship('product', 'id')
                    ->required(),
            ]);
    }
}
