<?php

declare(strict_types=1);

namespace App\Filament\Resources\Visibles\Pages;

use App\Filament\Resources\Visibles\VisibleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVisible extends CreateRecord
{
    protected static string $resource = VisibleResource::class;
}
