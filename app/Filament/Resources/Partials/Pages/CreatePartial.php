<?php

declare(strict_types=1);

namespace App\Filament\Resources\Partials\Pages;

use App\Filament\Resources\Partials\PartialResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePartial extends CreateRecord
{
    protected static string $resource = PartialResource::class;
}
