<?php

namespace App\Filament\Resources\VisibleResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\VisibleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVisibles extends ListRecords
{
    protected static string $resource = VisibleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
