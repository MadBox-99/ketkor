<?php

namespace App\Filament\Resources\Partials\Pages;

use App\Filament\Resources\Partials\PartialResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePartial extends CreateRecord
{
    protected static string $resource = PartialResource::class;
}
