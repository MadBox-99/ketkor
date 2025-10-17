<?php

namespace App\Filament\Resources\AccessTokens\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\AccessTokens\AccessTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccessTokens extends ListRecords
{
    protected static string $resource = AccessTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
