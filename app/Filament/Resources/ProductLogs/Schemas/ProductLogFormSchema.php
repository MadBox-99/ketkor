<?php

namespace App\Filament\Resources\ProductLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductLogFormSchema
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'id')
                    ->required(),
                TextInput::make('what')
                    ->maxLength(500),
                TextInput::make('comment')
                    ->maxLength(255),
                DateTimePicker::make('when')
                    ->required(),
            ]);
    }
}
