<?php

declare(strict_types=1);

use App\Filament\Pages\MaintenanceReminderSettingsPage;
use App\Models\MaintenanceReminderSetting;
use App\Models\Product;
use App\Models\Tool;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::findOrCreate('Admin', 'web');
    $user = User::factory()->createOne();
    $user->assignRole('Admin');

    actingAs($user);
});

it('fills the form with the current settings', function (): void {
    MaintenanceReminderSetting::current()->update(['contact_phone' => '+36 1 234 5678']);

    livewire(MaintenanceReminderSettingsPage::class)
        ->assertSchemaStateSet([
            'contact_phone' => '+36 1 234 5678',
            'overdue_repeat_days' => 14,
        ]);
});

it('saves the settings and the template', function (): void {
    livewire(MaintenanceReminderSettingsPage::class)
        ->fillForm([
            'enabled' => false,
            'advance_days' => ['45', '14'],
            'overdue_repeat_days' => 21,
            'overdue_max_count' => 2,
            'contact_phone' => '+36 1 111 2222',
            'contact_email' => 'szerviz@example.test',
            'booking_url' => 'https://example.test/foglalas',
            'email_subject' => 'Új tárgy {{ serial_number }}',
            'email_body' => 'Új törzs {{ due_date }}',
        ])
        ->call('save')
        ->assertNotified()
        ->assertHasNoFormErrors();

    $settings = MaintenanceReminderSetting::current();

    expect($settings->enabled)->toBeFalse()
        ->and($settings->advance_days)->toBe([45, 14])
        ->and($settings->overdue_repeat_days)->toBe(21)
        ->and($settings->overdue_max_count)->toBe(2)
        ->and($settings->email_subject)->toBe('Új tárgy {{ serial_number }}')
        ->and($settings->email_body)->toBe('Új törzs {{ due_date }}');
});

it('requires a subject and a body', function (): void {
    livewire(MaintenanceReminderSettingsPage::class)
        ->fillForm([
            'email_subject' => null,
            'email_body' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'email_subject' => 'required',
            'email_body' => 'required',
        ]);
});

it('previews the template with a sample product', function (): void {
    $tool = Tool::factory()->createOne(['name' => 'Vaillant ecoTEC']);
    Product::factory()->createOne([
        'tool_id' => $tool->id,
        'owner_name' => 'Kiss Béla',
        'serial_number' => 'AB-1234-CDEF',
        'installation_date' => '2025-02-14',
    ]);

    livewire(MaintenanceReminderSettingsPage::class)
        ->fillForm([
            'email_subject' => 'Tárgy {{ serial_number }}',
            'email_body' => '{{ owner_name }} / {{ tool_name }} / {{ due_date }}',
        ])
        ->call('preview')
        ->assertSet('previewSubject', 'Tárgy AB-1234-CDEF')
        ->assertSet('previewBody', 'Kiss Béla / Vaillant ecoTEC / 2026. 02. 14.');
});

it('warns when there is no product to preview with', function (): void {
    livewire(MaintenanceReminderSettingsPage::class)
        ->call('preview')
        ->assertNotified()
        ->assertSet('previewBody', null);
});

it('allows clearing the advance days to disable advance reminders', function (): void {
    livewire(MaintenanceReminderSettingsPage::class)
        ->fillForm([
            'advance_days' => [],
        ])
        ->call('save')
        ->assertNotified()
        ->assertHasNoFormErrors();

    expect(MaintenanceReminderSetting::current()->advance_days)->toBe([]);
});
