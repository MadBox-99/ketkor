<?php

namespace App\Filament\Resources\Partials\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Partials\PartialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPartials extends ListRecords
{
    protected static string $resource = PartialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
