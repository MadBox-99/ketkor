<?php

namespace App\Filament\Resources\AccessTokens\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AccessTokenFormSchema
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('token')
                    ->maxLength(40),
                Toggle::make('used')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'id')
                    ->required(),
            ]);
    }
}
