<?php

namespace App\Filament\Resources\PartialResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\PartialResource;
use Filament\Actions;
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
