<?php

namespace App\Filament\Resources\Visibles\Pages;

use App\Filament\Resources\Visibles\VisibleResource;
use Filament\Actions\CreateAction;
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
