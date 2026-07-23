<?php

declare(strict_types=1);

namespace App\Filament\Resources\Partials\Pages;

use App\Filament\Resources\Partials\PartialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListPartials extends ListRecords
{
    protected static string $resource = PartialResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
