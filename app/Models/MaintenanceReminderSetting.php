<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable([
    'enabled',
    'advance_days',
    'overdue_repeat_days',
    'overdue_max_count',
    'contact_phone',
    'contact_email',
    'booking_url',
    'email_subject',
    'email_body',
])]
class MaintenanceReminderSetting extends Model
{
    public const DEFAULT_SUBJECT = 'Esedékes karbantartás - {{ serial_number }}';

    public const DEFAULT_BODY = <<<'TEXT'
        Tisztelt {{ owner_name }}!

        Ezúton értesítjük, hogy a(z) {{ tool_name }} készülékének ({{ serial_number }}) {{ maintenance_type }} karbantartása {{ due_date }} napján esedékes.

        Előző karbantartás: {{ last_maintenance_date }}

        Kérjük, egyeztessen velünk időpontot:
        Telefon: {{ contact_phone }}
        E-mail: {{ contact_email }}

        Köszönjük a bizalmát!
        TEXT;

    /**
     * A rendszer egyetlen beállítás rekordja, szükség esetén létrehozva.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'enabled' => true,
            'advance_days' => [30, 7],
            'overdue_repeat_days' => 14,
            'overdue_max_count' => 3,
            'email_subject' => self::DEFAULT_SUBJECT,
            'email_body' => self::DEFAULT_BODY,
        ]);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'advance_days' => 'array',
            'overdue_repeat_days' => 'integer',
            'overdue_max_count' => 'integer',
        ];
    }
}
