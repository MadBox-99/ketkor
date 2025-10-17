<?php

namespace App\Filament\Resources\Organizations\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Organizations\OrganizationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrganizations extends ListRecords
{
    protected static string $resource = OrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
