<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductLogs\Schemas;

use App\Filament\Forms\Components\SignaturePad;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                Toggle::make('is_online')
                    ->label('Online')
                    ->live()
                    ->default(false),
                TextInput::make('worksheet_id')
                    ->label('Worksheet ID')
                    ->visible(fn (string $operation, array $get): bool => $get('is_online') === false),
                SignaturePad::make('signature')
                    ->label('Customer signature')
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->columnSpanFull(),
            ]);
    }
}
