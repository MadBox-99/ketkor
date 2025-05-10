<?php

namespace App\Filament\Resources\ProductLogResource\Pages;

use App\Filament\Resources\ProductLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductLog extends CreateRecord
{
    protected static string $resource = ProductLogResource::class;
}
