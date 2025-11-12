<?php

namespace App\Filament\Resources\Partials\Pages;

use App\Filament\Resources\Partials\PartialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPartial extends EditRecord
{
    protected static string $resource = PartialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
