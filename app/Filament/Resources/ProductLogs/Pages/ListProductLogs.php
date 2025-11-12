<?php

namespace App\Filament\Resources\ProductLogs\Pages;

use App\Filament\Resources\ProductLogs\ProductLogResource;
use Filament\Actions\CreateAction;
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
