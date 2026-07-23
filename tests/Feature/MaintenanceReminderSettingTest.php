<?php

declare(strict_types=1);

use App\Models\MaintenanceReminderSetting;

it('creates the settings row with defaults on first access', function (): void {
    $settings = MaintenanceReminderSetting::current();

    expect($settings->exists)->toBeTrue()
        ->and($settings->enabled)->toBeTrue()
        ->and($settings->advance_days)->toBe([30, 7])
        ->and($settings->overdue_repeat_days)->toBe(14)
        ->and($settings->overdue_max_count)->toBe(3)
        ->and($settings->email_subject)->not->toBeEmpty()
        ->and($settings->email_body)->toContain('{{ serial_number }}');
});

it('returns the same row on repeated access', function (): void {
    $first = MaintenanceReminderSetting::current();
    $second = MaintenanceReminderSetting::current();

    expect($second->getKey())->toBe($first->getKey())
        ->and(MaintenanceReminderSetting::query()->count())->toBe(1);
});

it('persists edited settings', function (): void {
    MaintenanceReminderSetting::current()->update([
        'advance_days' => [45, 14],
        'contact_phone' => '+36 1 234 5678',
    ]);

    $settings = MaintenanceReminderSetting::current();

    expect($settings->advance_days)->toBe([45, 14])
        ->and($settings->contact_phone)->toBe('+36 1 234 5678');
});
