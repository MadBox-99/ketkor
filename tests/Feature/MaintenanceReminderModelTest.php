<?php

declare(strict_types=1);

use App\Enums\MaintenanceReminderStage;
use App\Enums\MaintenanceReminderStatus;
use App\Models\MaintenanceReminder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;

function reminderAttributes(Product $product, User $user, array $overrides = []): array
{
    return array_merge([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'email' => $user->email,
        'stage' => MaintenanceReminderStage::Advance,
        'stage_key' => 30,
        'due_date' => '2026-03-10',
        'last_maintenance_at' => '2025-03-10',
        'sent_at' => now(),
        'status' => MaintenanceReminderStatus::Sent,
    ], $overrides);
}

it('stores a reminder with casted enums and dates', function (): void {
    $product = Product::factory()->createOne();
    $user = User::factory()->createOne();

    $reminder = MaintenanceReminder::query()->create(reminderAttributes($product, $user));

    expect($reminder->stage)->toBe(MaintenanceReminderStage::Advance)
        ->and($reminder->status)->toBe(MaintenanceReminderStatus::Sent)
        ->and($reminder->due_date->toDateString())->toBe('2026-03-10')
        ->and($reminder->product->is($product))->toBeTrue()
        ->and($reminder->user->is($user))->toBeTrue();
});

it('rejects a duplicate reminder for the same product, recipient, due date and stage', function (): void {
    $product = Product::factory()->createOne();
    $user = User::factory()->createOne();

    MaintenanceReminder::query()->create(reminderAttributes($product, $user));

    expect(fn () => MaintenanceReminder::query()->create(reminderAttributes($product, $user)))
        ->toThrow(QueryException::class);
});

it('allows the same due date for a different stage key', function (): void {
    $product = Product::factory()->createOne();
    $user = User::factory()->createOne();

    MaintenanceReminder::query()->create(reminderAttributes($product, $user));
    MaintenanceReminder::query()->create(reminderAttributes($product, $user, ['stage_key' => 7]));

    expect(MaintenanceReminder::query()->count())->toBe(2);
});

it('allows the same stage for a different recipient', function (): void {
    $product = Product::factory()->createOne();

    MaintenanceReminder::query()->create(reminderAttributes($product, User::factory()->createOne()));
    MaintenanceReminder::query()->create(reminderAttributes($product, User::factory()->createOne()));

    expect(MaintenanceReminder::query()->count())->toBe(2);
});
