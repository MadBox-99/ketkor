<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductLogs\Pages;

use App\Filament\Resources\ProductLogs\ProductLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductLog extends CreateRecord
{
    protected static string $resource = ProductLogResource::class;
}
