<?php

declare(strict_types=1);

use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Mail\MaintenanceReminderMail;
use App\Models\MaintenanceReminder;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Mail::fake();

    Role::findOrCreate('Admin', 'web');
    $admin = User::factory()->createOne();
    $admin->assignRole('Admin');

    actingAs($admin);
});

function remindableProduct(): Product
{
    $product = Product::factory()->createOne([
        'installation_date' => '2024-01-01',
        'warrantee_date' => '2030-01-01',
    ]);

    ProductLog::factory()->on('2025-03-10 09:00:00')->createOne(['product_id' => $product->id]);
    $product->users()->attach(User::factory()->createOne());

    return $product->fresh();
}

it('edits the maintenance interval and the reminder switch on the product form', function (): void {
    $product = remindableProduct();

    livewire(EditProduct::class, ['record' => $product->id])
        ->fillForm([
            'maintenance_interval_months' => 6,
            'maintenance_reminders_enabled' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->fresh()->maintenance_interval_months)->toBe(6)
        ->and($product->fresh()->maintenance_reminders_enabled)->toBeFalse();
});

it('shows the next due date in the product table', function (): void {
    $product = remindableProduct();

    livewire(ListProducts::class)
        ->assertCanSeeTableRecords([$product])
        ->assertTableColumnStateSet('next_maintenance_due_date', '2026-03-10', $product);
});

it('sends a manual reminder from the product table', function (): void {
    $product = remindableProduct();

    livewire(ListProducts::class)
        ->callAction(TestAction::make('sendMaintenanceReminder')->table($product))
        ->assertNotified();

    Mail::assertQueued(MaintenanceReminderMail::class, 1);
    expect(MaintenanceReminder::query()->count())->toBe(1);
});

it('reports when a manual reminder cannot be sent', function (): void {
    $product = remindableProduct();
    $product->update(['warrantee_date' => '2020-01-01']);

    livewire(ListProducts::class)
        ->callAction(TestAction::make('sendMaintenanceReminder')->table($product))
        ->assertNotified();

    Mail::assertNothingQueued();
    expect(MaintenanceReminder::query()->count())->toBe(0);
});

it('reports when a manual reminder cannot be computed due date', function (): void {
    $product = Product::factory()->createOne([
        'installation_date' => null,
        'warrantee_date' => '2030-01-01',
    ]);

    $product->users()->attach(User::factory()->createOne());

    livewire(ListProducts::class)
        ->callAction(TestAction::make('sendMaintenanceReminder')->table($product))
        ->assertNotified();

    Mail::assertNothingQueued();
    expect(MaintenanceReminder::query()->count())->toBe(0);
});

it('edits the reminder switch on the user form', function (): void {
    $user = User::factory()->createOne();

    livewire(EditUser::class, ['record' => $user->id])
        ->fillForm(['maintenance_reminders_enabled' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->maintenance_reminders_enabled)->toBeFalse();
});
