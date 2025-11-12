<?php

namespace App\Filament\Resources\Logs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LogFormSchema
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Textarea::make('what')
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('when'),
            ]);
    }
}
