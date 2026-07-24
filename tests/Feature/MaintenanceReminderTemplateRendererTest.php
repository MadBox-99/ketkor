<?php

declare(strict_types=1);

use App\Enums\MaintenanceReminderStage;
use App\Models\MaintenanceReminderSetting;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\Tool;
use App\Models\User;
use App\Support\MaintenanceReminderTemplateRenderer;
use App\Support\MaintenanceSchedule;
use App\Support\PendingMaintenanceReminder;

function pendingReminder(array $productAttributes = [], int $intervalMonths = 12): PendingMaintenanceReminder
{
    $tool = Tool::factory()->createOne(['name' => 'Vaillant ecoTEC']);
    $product = Product::factory()->createOne(array_merge([
        'tool_id' => $tool->id,
        'owner_name' => 'Kiss Béla',
        'serial_number' => 'AB-1234-CDEF',
        'installation_date' => '2024-01-01',
        'maintenance_interval_months' => $intervalMonths,
    ], $productAttributes));

    ProductLog::factory()->on('2025-03-10 09:00:00')->createOne(['product_id' => $product->id]);

    return new PendingMaintenanceReminder(
        product: $product->fresh(),
        user: User::factory()->createOne(),
        stage: MaintenanceReminderStage::Advance,
        stageKey: 30,
        schedule: MaintenanceSchedule::for($product->fresh()),
    );
}

function pendingReminderWithoutMaintenanceLog(array $productAttributes = [], int $intervalMonths = 12): PendingMaintenanceReminder
{
    $tool = Tool::factory()->createOne(['name' => 'Vaillant ecoTEC']);
    $product = Product::factory()->createOne(array_merge([
        'tool_id' => $tool->id,
        'owner_name' => 'Kiss Béla',
        'serial_number' => 'AB-1234-CDEF',
        'installation_date' => '2024-01-01',
        'maintenance_interval_months' => $intervalMonths,
    ], $productAttributes));

    return new PendingMaintenanceReminder(
        product: $product->fresh(),
        user: User::factory()->createOne(),
        stage: MaintenanceReminderStage::Advance,
        stageKey: 30,
        schedule: MaintenanceSchedule::for($product->fresh()),
    );
}

it('replaces every supported variable', function (): void {
    MaintenanceReminderSetting::current()->update([
        'contact_phone' => '+36 1 234 5678',
        'contact_email' => 'szerviz@example.test',
        'booking_url' => 'https://example.test/foglalas',
        'email_subject' => 'Karbantartás: {{ serial_number }}',
        'email_body' => '{{ owner_name }} | {{ tool_name }} | {{ maintenance_type }} | '
            . '{{ last_maintenance_date }} | {{ due_date }} | {{ contact_phone }} | '
            . '{{ contact_email }} | {{ booking_url }}',
    ]);

    $rendered = new MaintenanceReminderTemplateRenderer()
        ->render(pendingReminder(), MaintenanceReminderSetting::current());

    expect($rendered['subject'])->toBe('Karbantartás: AB-1234-CDEF')
        ->and($rendered['body'])->toBe(
            'Kiss Béla | Vaillant ecoTEC | éves | 2025. 03. 10. | 2026. 03. 10. | '
            . '+36 1 234 5678 | szerviz@example.test | https://example.test/foglalas',
        );
});

it('renders an empty last maintenance date when there is no maintenance log', function (): void {
    MaintenanceReminderSetting::current()->update(['email_body' => 'Előző karbantartás: [{{ last_maintenance_date }}]']);

    $rendered = new MaintenanceReminderTemplateRenderer()
        ->render(pendingReminderWithoutMaintenanceLog(), MaintenanceReminderSetting::current());

    expect($rendered['body'])->toBe('Előző karbantartás: []');
});

it('labels a six month interval as half-yearly', function (): void {
    MaintenanceReminderSetting::current()->update(['email_body' => '{{ maintenance_type }}']);

    $rendered = new MaintenanceReminderTemplateRenderer()
        ->render(pendingReminder(intervalMonths: 6), MaintenanceReminderSetting::current());

    expect($rendered['body'])->toBe('féléves');
});

it('replaces unknown variables with an empty string', function (): void {
    MaintenanceReminderSetting::current()->update(['email_body' => 'A[{{ nincs_ilyen }}]B']);

    $rendered = new MaintenanceReminderTemplateRenderer()
        ->render(pendingReminder(), MaintenanceReminderSetting::current());

    expect($rendered['body'])->toBe('A[]B');
});

it('tolerates missing optional contact details', function (): void {
    MaintenanceReminderSetting::current()->update([
        'contact_phone' => null,
        'email_body' => 'Tel: [{{ contact_phone }}]',
    ]);

    $rendered = new MaintenanceReminderTemplateRenderer()
        ->render(pendingReminder(), MaintenanceReminderSetting::current());

    expect($rendered['body'])->toBe('Tel: []');
});

it('handles variables written without spaces', function (): void {
    MaintenanceReminderSetting::current()->update(['email_body' => '{{serial_number}}']);

    $rendered = new MaintenanceReminderTemplateRenderer()
        ->render(pendingReminder(), MaintenanceReminderSetting::current());

    expect($rendered['body'])->toBe('AB-1234-CDEF');
});
