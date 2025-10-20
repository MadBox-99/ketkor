<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProductCategory: string implements HasLabel
{
    case SIME = 'sime';
    case FERROLI = 'ferroli';
    case SPRSUN = 'sprsun';
    case SUNRAIN = 'sunrain';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::SIME => 'SIME',
            self::FERROLI => 'Ferroli',
            self::SPRSUN => 'SPRsun',
            self::SUNRAIN => 'Sunrain',
        };
    }
}
