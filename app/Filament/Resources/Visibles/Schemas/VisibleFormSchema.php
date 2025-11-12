<?php

declare(strict_types=1);

namespace App\Filament\Resources\Visibles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VisibleFormSchema
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('isVisible')
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'id')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
            ]);
    }
}
