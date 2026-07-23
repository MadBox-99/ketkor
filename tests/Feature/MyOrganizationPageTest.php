<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Organizations\MyOrganization;
use App\Models\Organization;
use App\Models\Product;
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

it('refuses to move a product to a non-existent user without an unhandled error', function (): void {
    $organization = Organization::factory()->createOne();
    $organizer = User::factory()->createOne(['organization_id' => $organization->id]);
    $organizer->assignRole(UserRole::Organizer);
    $fromUser = User::factory()->createOne(['organization_id' => $organization->id]);
    $product = Product::factory()->createOne();
    $fromUser->products()->attach($product);

    $nonExistentUserId = User::query()->max('id') + 1;

    actingAs($organizer);

    livewire(MyOrganization::class)->call('moveProduct', $product->id, $fromUser->id, $nonExistentUserId);

    expect($fromUser->products()->whereKey($product->id)->exists())->toBeTrue()
        ->and(User::query()->whereKey($nonExistentUserId)->exists())->toBeFalse();
});

it('refuses to move a product to a user outside the organizer\'s organization', function (): void {
    $organization = Organization::factory()->createOne();
    $organizer = User::factory()->createOne(['organization_id' => $organization->id]);
    $organizer->assignRole(UserRole::Organizer);
    $fromUser = User::factory()->createOne(['organization_id' => $organization->id]);
    $outsider = User::factory()->createOne(['organization_id' => null]);
    $product = Product::factory()->createOne();
    $fromUser->products()->attach($product);

    actingAs($organizer);

    livewire(MyOrganization::class)->call('moveProduct', $product->id, $fromUser->id, $outsider->id);

    expect($fromUser->products()->whereKey($product->id)->exists())->toBeTrue()
        ->and($outsider->products()->whereKey($product->id)->exists())->toBeFalse();
});

it('refuses to move a product away from a user outside the organizer\'s organization', function (): void {
    $organization = Organization::factory()->createOne();
    $organizer = User::factory()->createOne(['organization_id' => $organization->id]);
    $organizer->assignRole(UserRole::Organizer);
    $outsiderFromUser = User::factory()->createOne(['organization_id' => null]);
    $toUser = User::factory()->createOne(['organization_id' => $organization->id]);
    $product = Product::factory()->createOne();
    $outsiderFromUser->products()->attach($product);

    actingAs($organizer);

    livewire(MyOrganization::class)->call('moveProduct', $product->id, $outsiderFromUser->id, $toUser->id);

    expect($outsiderFromUser->products()->whereKey($product->id)->exists())->toBeTrue()
        ->and($toUser->products()->whereKey($product->id)->exists())->toBeFalse();
});

it('initialises the move-product selector to a real user id instead of null', function (): void {
    $organization = Organization::factory()->createOne();
    $organizer = User::factory()->createOne(['organization_id' => $organization->id]);
    $organizer->assignRole(UserRole::Organizer);
    $product = Product::factory()->createOne();
    $organizer->products()->attach($product);

    actingAs($organizer);

    get(route('organizations.myorganization'))
        ->assertOk()
        ->assertDontSee('selectedUserId: null', false)
        ->assertSee('selectedUserId: ' . $organizer->id . ' }', false);
});

it('moves a product from one member to another', function (): void {
    $organization = Organization::factory()->createOne();
    $organizer = User::factory()->createOne(['organization_id' => $organization->id]);
    $organizer->assignRole(UserRole::Organizer);
    $fromUser = User::factory()->createOne(['organization_id' => $organization->id]);
    $toUser = User::factory()->createOne(['organization_id' => $organization->id]);
    $product = Product::factory()->createOne();
    $fromUser->products()->attach($product);

    actingAs($organizer);

    livewire(MyOrganization::class)->call('moveProduct', $product->id, $fromUser->id, $toUser->id);

    expect($fromUser->products()->whereKey($product->id)->exists())->toBeFalse()
        ->and($toUser->products()->whereKey($product->id)->exists())->toBeTrue();
});
