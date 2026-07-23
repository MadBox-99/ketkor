<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductLogs\Pages;

use App\Filament\Resources\ProductLogs\ProductLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListProductLogs extends ListRecords
{
    protected static string $resource = ProductLogResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
