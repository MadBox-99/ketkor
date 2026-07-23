<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MaintenanceReminderStage: string implements HasLabel
{
    case Advance = 'advance';

    case Overdue = 'overdue';

    case Manual = 'manual';

    public function getLabel(): string
    {
        return match ($this) {
            self::Advance => 'Előzetes',
            self::Overdue => 'Lejárt',
            self::Manual => 'Manuális',
        };
    }
}
