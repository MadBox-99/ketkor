<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\MaintenanceReminderSetting;

final class MaintenanceReminderTemplateRenderer
{
    private const DATE_FORMAT = 'Y. m. d.';

    /**
     * A tárgy és a törzs feloldott változókkal.
     *
     * @return array{subject: string, body: string}
     */
    public function render(
        PendingMaintenanceReminder $reminder,
        MaintenanceReminderSetting $settings,
    ): array {
        $variables = $this->variables($reminder, $settings);

        return [
            'subject' => $this->replace($settings->email_subject, $variables),
            'body' => $this->replace($settings->email_body, $variables),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function variables(
        PendingMaintenanceReminder $reminder,
        MaintenanceReminderSetting $settings,
    ): array {
        $product = $reminder->product;

        return [
            'owner_name' => (string) ($product->owner_name ?? ''),
            'serial_number' => (string) $product->serial_number,
            'tool_name' => (string) ($product->tool?->name ?? ''),
            'maintenance_type' => $product->maintenance_interval_months === 6 ? 'féléves' : 'éves',
            'last_maintenance_date' => $reminder->schedule->fromMaintenanceLog
                ? $reminder->schedule->baseDate->format(self::DATE_FORMAT)
                : '',
            'due_date' => $reminder->schedule->dueDate->format(self::DATE_FORMAT),
            'contact_phone' => (string) ($settings->contact_phone ?? ''),
            'contact_email' => (string) ($settings->contact_email ?? ''),
            'booking_url' => (string) ($settings->booking_url ?? ''),
        ];
    }

    /**
     * @param  array<string, string>  $variables
     */
    private function replace(string $template, array $variables): string
    {
        return (string) preg_replace_callback(
            '/\{\{\s*(\w+)\s*\}\}/',
            fn (array $matches): string => $variables[$matches[1]] ?? '',
            $template,
        );
    }
}
