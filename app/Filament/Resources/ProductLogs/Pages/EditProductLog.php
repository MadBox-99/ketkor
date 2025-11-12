<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductLogs\Pages;

use App\Filament\Resources\ProductLogs\ProductLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductLog extends EditRecord
{
    protected static string $resource = ProductLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
