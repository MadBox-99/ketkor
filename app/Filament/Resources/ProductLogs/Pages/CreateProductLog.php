<?php

namespace App\Filament\Resources\ProductLogs\Pages;

use App\Filament\Resources\ProductLogs\ProductLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductLog extends CreateRecord
{
    protected static string $resource = ProductLogResource::class;
}
