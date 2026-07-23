<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Organizations\MyOrganization;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    foreach (UserRole::cases() as $role) {
        Role::query()->firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }
});

it('links the create employee button to the employee create route', function (): void {
    $organization = Organization::factory()->createOne([
        'name' => 'Test Org',
    ]);
    $organizer = User::factory()->createOne([
        'organization_id' => $organization->id,
    ]);
    $organizer->assignRole(UserRole::Organizer);

    actingAs($organizer);

    get(route('organizations.myorganization'))->assertOk()->assertSeeHtml(route('organizations.employee.create'))->assertDontSeeHtml('href="' . route('organizations.create') . '"')->assertSeeLivewire(MyOrganization::class);
});

it('no longer exposes GET routes for mutations', function (string $name): void {
    expect(Route::has($name))->toBeFalse();
})->with([
    'organizations.detach',
    'organizations.removeUserFromOrganization',
    'organizations.productMove',
    'organizations.myorganizationupdate',
]);

it('refuses to remove a member from a different organization', function (): void {
    $organization = Organization::factory()->createOne();
    $organizer = User::factory()->createOne(['organization_id' => $organization->id]);
    $outsider = User::factory()->createOne(['organization_id' => null]);

    actingAs($organizer);

    livewire(MyOrganization::class)->call('removeMember', $outsider->id);

    expect(User::query()->whereKey($outsider->id)->exists())->toBeTrue();
});

it('redirects a user with no organization away from the my-organization page', function (): void {
    $actor = User::factory()->createOne(['organization_id' => null]);
    $actor->assignRole(UserRole::Organizer);

    actingAs($actor);

    get(route('organizations.myorganization'))->assertRedirect(route('organizations.create'));
});

it('refuses to remove a member whose organization is null', function (): void {
    $organization = Organization::factory()->createOne();
    $organizer = User::factory()->createOne(['organization_id' => $organization->id]);
    $organizer->assignRole(UserRole::Organizer);
    $target = User::factory()->createOne(['organization_id' => null]);

    actingAs($organizer);

    livewire(MyOrganization::class)->call('removeMember', $target->id);

    expect(User::query()->whereKey($target->id)->exists())->toBeTrue();
});
