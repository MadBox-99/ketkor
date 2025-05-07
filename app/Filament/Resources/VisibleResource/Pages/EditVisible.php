<?php

namespace App\Filament\Resources\VisibleResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\VisibleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVisible extends EditRecord
{
    protected static string $resource = VisibleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
