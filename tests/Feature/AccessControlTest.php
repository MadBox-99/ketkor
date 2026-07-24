<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Products\Edit;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Tool;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    foreach (UserRole::cases() as $role) {
        Role::findOrCreate($role->value, 'web');
    }
});

function userWithRole(string $role): User
{
    $user = User::factory()->createOne();
    $user->assignRole($role);

    return $user;
}

function aProduct(): Product
{
    return Product::factory()->createOne([
        'tool_id' => Tool::factory()->createOne()->id,
        'purchase_date' => now()->subMonths(3),
    ]);
}

describe('organization management routes', function (): void {
    it('forbids a non-privileged user from creating an organization', function (): void {
        actingAs(userWithRole(UserRole::Servicer->value));

        get(route('organizations.create'))->assertForbidden();
    });

    it('forbids a non-privileged user from editing any organization', function (): void {
        actingAs(userWithRole(UserRole::Organizer->value));
        $organization = Organization::factory()->createOne();

        get(route('organizations.edit', ['organization' => $organization]))->assertForbidden();
    });

    it('allows an operator to create and edit organizations', function (): void {
        actingAs(userWithRole(UserRole::Operator->value));
        $organization = Organization::factory()->createOne();

        get(route('organizations.create'))->assertOk();
        get(route('organizations.edit', ['organization' => $organization]))->assertOk();
    });
});

describe('product edit authorization', function (): void {
    it('forbids a non-admin from editing a product not assigned to them', function (): void {
        actingAs(userWithRole(UserRole::Servicer->value));
        $product = aProduct();

        get(route('products.edit', ['product' => $product]))->assertForbidden();
    });

    it('allows a non-admin to edit a product assigned to them', function (): void {
        $user = userWithRole(UserRole::Servicer->value);
        actingAs($user);
        $product = aProduct();
        $product->users()->attach($user);

        get(route('products.edit', ['product' => $product]))->assertOk();
    });

    it('allows an operator to edit any product', function (): void {
        actingAs(userWithRole(UserRole::Operator->value));

        get(route('products.edit', ['product' => aProduct()]))->assertOk();
    });

    it('does not expose the full user list to a non-admin editor', function (): void {
        $user = userWithRole(UserRole::Servicer->value);
        actingAs($user);
        User::factory()->count(3)->create();
        $product = aProduct();
        $product->users()->attach($user);

        Livewire::test(Edit::class, ['product' => $product])
            ->assertSet('users', fn ($users): bool => $users->isEmpty());
    });

    it('loads the full user list for a management-role editor', function (): void {
        actingAs(userWithRole(UserRole::Operator->value));
        User::factory()->count(3)->create();

        Livewire::test(Edit::class, ['product' => aProduct()])
            ->assertSet('users', fn ($users): bool => $users->isNotEmpty());
    });
});
