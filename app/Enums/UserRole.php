<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case Admin = 'Admin';
    case SuperAdmin = 'Super Admin';
    case Operator = 'Operator';
    case Servicer = 'Servicer';
    case Organizer = 'Organizer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::SuperAdmin => 'Super Admin',
            self::Operator => 'Operator',
            self::Servicer => 'Servicer',
            self::Organizer => 'Organizer',
        };
    }
}
