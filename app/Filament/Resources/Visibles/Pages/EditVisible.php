<?php

declare(strict_types=1);

namespace App\Filament\Resources\Visibles\Pages;

use App\Filament\Resources\Visibles\VisibleResource;
use Filament\Actions\DeleteAction;
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
