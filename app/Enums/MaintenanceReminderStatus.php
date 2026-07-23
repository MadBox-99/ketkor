<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MaintenanceReminderStatus: string implements HasLabel
{
    case Sent = 'sent';

    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Sent => 'Elküldve',
            self::Failed => 'Hiba',
        };
    }
}
