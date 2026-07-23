<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Organizations\CreateEmployee;
use App\Livewire\Profile\Edit;
use App\Models\Organization;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    foreach (UserRole::cases() as $role) {
        Role::query()->firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }
});

it('renders the employee create page', function (): void {
    $organization = Organization::factory()->createOne();
    $organizer = User::factory()->createOne(['organization_id' => $organization->id]);
    $organizer->assignRole(UserRole::Organizer);

    actingAs($organizer);

    get(route('organizations.employee.create'))
        ->assertOk()
        ->assertSeeLivewire(CreateEmployee::class);
});

it('creates an employee in the organizer own organization', function (): void {
    $organization = Organization::factory()->createOne();
    $organizer = User::factory()->createOne(['organization_id' => $organization->id]);
    $organizer->assignRole(UserRole::Organizer);

    actingAs($organizer);

    livewire(CreateEmployee::class)
        ->set('name', 'New Employee')
        ->set('email', 'employee@example.com')
        ->set('password', 'password123')
        ->call('save')
        ->assertRedirect(route('organizations.myorganization'));

    assertDatabaseHas(User::class, [
        'email' => 'employee@example.com',
        'organization_id' => $organization->id,
    ]);
});

it('renders the profile page', function (): void {
    actingAs(User::factory()->createOne());

    get(route('profile.edit'))->assertOk()->assertSeeLivewire(Edit::class);
});

it('updates the profile', function (): void {
    $user = User::factory()->createOne(['name' => 'Old']);
    actingAs($user);

    livewire(Edit::class)
        ->set('name', 'New')
        ->call('save');

    expect($user->fresh()->name)->toBe('New');
});

it('deletes the account when the current password is correct', function (): void {
    $user = User::factory()->createOne();
    actingAs($user);

    livewire(Edit::class)
        ->set('password', 'password')
        ->call('destroy');

    assertDatabaseMissing(User::class, ['id' => $user->id]);
    assertGuest();
});

it('does not delete the account when the current password is incorrect', function (): void {
    $user = User::factory()->createOne();
    actingAs($user);

    livewire(Edit::class)
        ->set('password', 'wrong-password')
        ->call('destroy')
        ->assertHasErrors(['password']);

    assertDatabaseHas(User::class, ['id' => $user->id]);
});

it('does not delete the account when the password is empty', function (): void {
    $user = User::factory()->createOne();
    actingAs($user);

    livewire(Edit::class)
        ->set('password', '')
        ->call('destroy')
        ->assertHasErrors(['password']);

    assertDatabaseHas(User::class, ['id' => $user->id]);
});
