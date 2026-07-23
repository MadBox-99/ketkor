<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Organizations\Index;
use App\Livewire\Organizations\UsersTable;
use App\Models\Organization;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    foreach (UserRole::cases() as $role) {
        Role::query()->firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }

    $admin = User::factory()->createOne();
    $admin->assignRole(UserRole::Admin);

    actingAs($admin);
});

it('renders the organizations index', function (): void {
    get(route('organizations.index'))->assertOk()->assertSeeLivewire(Index::class);
});

it('lists organizations in the table', function (): void {
    $organizations = Organization::factory()->count(3)->create();

    livewire(Index::class)->assertCanSeeTableRecords($organizations);
});

it('lists only the given organization members in the users table', function (): void {
    $organization = Organization::factory()->createOne();
    $member = User::factory()->createOne(['organization_id' => $organization->id]);
    $outsider = User::factory()->createOne();

    livewire(UsersTable::class, ['organization' => $organization->id])
        ->assertCanSeeTableRecords([$member])
        ->assertCanNotSeeTableRecords([$outsider]);
});

it('forbids a user without a privileged role from viewing the organizations index', function (): void {
    actingAs(User::factory()->createOne());

    get(route('organizations.index'))->assertForbidden();
});

it('allows an admin to view the organizations index', function (): void {
    get(route('organizations.index'))->assertOk();
});

it('prevents a non-privileged user from deleting an organization via the table action', function (): void {
    $organization = Organization::factory()->createOne();

    actingAs(User::factory()->createOne());

    livewire(Index::class)->callAction(TestAction::make('delete')->table($organization));

    expect(Organization::query()->whereKey($organization->id)->exists())->toBeTrue();
});
