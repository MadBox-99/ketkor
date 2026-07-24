<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tools\Schemas;

use App\Enums\ProductCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ToolFormSchema
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Név')
                    ->required()
                    ->maxLength(200),
                Select::make('category')
                    ->label('Kategória')
                    ->enum(ProductCategory::class)
                    ->options(ProductCategory::class)
                    ->nullable(),
                TextInput::make('tag')
                    ->label('Címke')
                    ->maxLength(200),
                TextInput::make('factory_name')
                    ->label('Gyártó')
                    ->maxLength(200),
            ]);
    }
}
