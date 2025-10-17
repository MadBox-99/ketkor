<?php

namespace App\Filament\Resources\Organizations\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Organizations\OrganizationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrganization extends EditRecord
{
    protected static string $resource = OrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
