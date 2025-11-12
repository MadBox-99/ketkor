<?php

declare(strict_types=1);

namespace App\Filament\Resources\Logs\Pages;

use App\Filament\Resources\Logs\LogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLog extends CreateRecord
{
    protected static string $resource = LogResource::class;
}
