<?php

declare(strict_types=1);

use App\Enums\MaintenanceReminderStage;
use App\Enums\MaintenanceReminderStatus;
use App\Filament\Resources\MaintenanceReminders\MaintenanceReminderResource;
use App\Filament\Resources\MaintenanceReminders\Pages\ListMaintenanceReminders;
use App\Models\MaintenanceReminder;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Role;

function adminUser(): User
{
    Role::findOrCreate('Admin', 'web');
    $user = User::factory()->createOne();
    $user->assignRole('Admin');

    return $user;
}

function reminderRecord(MaintenanceReminderStatus $status, int $stageKey = 30): MaintenanceReminder
{
    return MaintenanceReminder::query()->create([
        'product_id' => Product::factory()->createOne()->id,
        'user_id' => User::factory()->createOne()->id,
        'email' => fake()->unique()->safeEmail(),
        'stage' => MaintenanceReminderStage::Advance,
        'stage_key' => $stageKey,
        'due_date' => '2026-03-10',
        'last_maintenance_at' => '2025-03-10',
        'sent_at' => $status === MaintenanceReminderStatus::Sent ? now() : null,
        'status' => $status,
        'error' => $status === MaintenanceReminderStatus::Failed ? 'SMTP hiba' : null,
    ]);
}

beforeEach(function (): void {
    actingAs(adminUser());
});

it('lists the sent reminders', function (): void {
    $reminders = collect([
        reminderRecord(MaintenanceReminderStatus::Sent, 30),
        reminderRecord(MaintenanceReminderStatus::Failed, 7),
    ]);

    livewire(ListMaintenanceReminders::class)
        ->assertCanSeeTableRecords($reminders);
});

it('filters by status', function (): void {
    $sent = reminderRecord(MaintenanceReminderStatus::Sent, 30);
    $failed = reminderRecord(MaintenanceReminderStatus::Failed, 7);

    livewire(ListMaintenanceReminders::class)
        ->filterTable('status', MaintenanceReminderStatus::Failed->value)
        ->assertCanSeeTableRecords([$failed])
        ->assertCanNotSeeTableRecords([$sent]);
});

it('is read only', function (): void {
    expect(MaintenanceReminderResource::canCreate())->toBeFalse()
        ->and(MaintenanceReminderResource::getPages())->toHaveKeys(['index'])
        ->and(MaintenanceReminderResource::getPages())->toHaveCount(1);
});
