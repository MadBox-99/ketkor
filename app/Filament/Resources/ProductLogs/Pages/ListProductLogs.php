<?php

namespace App\Filament\Resources\ProductLogResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ProductLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductLogs extends ListRecords
{
    protected static string $resource = ProductLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
