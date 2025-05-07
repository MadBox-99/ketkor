<?php

namespace App\Filament\Resources\PartialResource\Pages;

use App\Filament\Resources\PartialResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePartial extends CreateRecord
{
    protected static string $resource = PartialResource::class;
}
