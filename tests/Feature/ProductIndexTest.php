<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Products\Index;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    foreach (UserRole::cases() as $role) {
        Role::query()->firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }
});

it('renders as a full-page livewire component', function (): void {
    $user = User::factory()->createOne();
    $user->assignRole(UserRole::Admin);
    actingAs($user);

    get(route('products.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

it('lists products in the table', function (): void {
    $user = User::factory()->createOne();
    $user->assignRole(UserRole::Admin);
    actingAs($user);

    $products = Product::factory()->count(3)->create();

    livewire(Index::class)
        ->assertCanSeeTableRecords($products);
});

it('forbids a user without a privileged role from viewing the products index', function (): void {
    actingAs(User::factory()->createOne());

    get(route('products.index'))->assertForbidden();
});

it('allows an admin to view the products index', function (): void {
    $user = User::factory()->createOne();
    $user->assignRole(UserRole::Admin);
    actingAs($user);

    get(route('products.index'))->assertOk();
});
