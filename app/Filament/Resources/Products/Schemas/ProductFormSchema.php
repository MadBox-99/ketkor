<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
            ]);
    }
}
