<?php

use App\Enums\ProductCategory;
use App\Livewire\ProductSearchUser;
use App\Models\Product;
use App\Models\Tool;
use App\Models\User;
use App\Models\Visible;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('renders the product search user component', function () {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(ProductSearchUser::class)
        ->assertStatus(200);
});

it('displays user products in the table', function () {
    $user = User::factory()->create();
    $tool = Tool::factory()->create([
        'name' => 'Test Tool',
        'category' => ProductCategory::KAZAN,
    ]);

    $product = Product::factory()->create([
        'serial_number' => 'TEST-123',
        'tool_id' => $tool->id,
        'city' => 'Test City',
        'street' => 'Test Street',
    ]);

    $product->users()->attach($user->id);

    Visible::factory()->create([
        'product_id' => $product->id,
        'isVisible' => true,
    ]);

    actingAs($user);

    Livewire::test(ProductSearchUser::class)
        ->assertCanSeeTableRecords([$product])
        ->assertSee('TEST-123');
});

it('filters products by serial number', function () {
    $user = User::factory()->create();
    $tool = Tool::factory()->create();

    $product1 = Product::factory()->create([
        'serial_number' => 'ABC-123',
        'tool_id' => $tool->id,
    ]);
    $product2 = Product::factory()->create([
        'serial_number' => 'XYZ-456',
        'tool_id' => $tool->id,
    ]);

    $product1->users()->attach($user->id);
    $product2->users()->attach($user->id);

    Visible::factory()->create(['product_id' => $product1->id, 'isVisible' => true]);
    Visible::factory()->create(['product_id' => $product2->id, 'isVisible' => true]);

    actingAs($user);

    Livewire::test(ProductSearchUser::class)
        ->filterTable('serial_number', ['value' => 'ABC'])
        ->assertCanSeeTableRecords([$product1])
        ->assertCanNotSeeTableRecords([$product2]);
});

it('filters products by tool name', function () {
    $user = User::factory()->create();
    $tool1 = Tool::factory()->create(['name' => 'Drill']);
    $tool2 = Tool::factory()->create(['name' => 'Saw']);

    $product1 = Product::factory()->create([
        'serial_number' => 'DRILL-001',
        'tool_id' => $tool1->id,
    ]);
    $product2 = Product::factory()->create([
        'serial_number' => 'SAW-001',
        'tool_id' => $tool2->id,
    ]);

    $product1->users()->attach($user->id);
    $product2->users()->attach($user->id);

    Visible::factory()->create(['product_id' => $product1->id, 'isVisible' => true]);
    Visible::factory()->create(['product_id' => $product2->id, 'isVisible' => true]);

    actingAs($user);

    Livewire::test(ProductSearchUser::class)
        ->filterTable('tool_name', ['value' => 'Drill'])
        ->assertCanSeeTableRecords([$product1])
        ->assertCanNotSeeTableRecords([$product2]);
});

it('filters products by warranty date range', function () {
    $user = User::factory()->create();
    $tool = Tool::factory()->create();

    $product1 = Product::factory()->create([
        'serial_number' => 'OLD-001',
        'tool_id' => $tool->id,
        'warrantee_date' => now()->subYear(),
    ]);
    $product2 = Product::factory()->create([
        'serial_number' => 'NEW-001',
        'tool_id' => $tool->id,
        'warrantee_date' => now()->addYear(),
    ]);

    $product1->users()->attach($user->id);
    $product2->users()->attach($user->id);

    Visible::factory()->create(['product_id' => $product1->id, 'isVisible' => true]);
    Visible::factory()->create(['product_id' => $product2->id, 'isVisible' => true]);

    actingAs($user);

    Livewire::test(ProductSearchUser::class)
        ->filterTable('warranty_date', [
            'from' => now()->addMonths(6)->format('Y-m-d'),
            'to' => now()->addMonths(18)->format('Y-m-d'),
        ])
        ->assertCanSeeTableRecords([$product2])
        ->assertCanNotSeeTableRecords([$product1]);
});

it('can remove product from user list', function () {
    $user = User::factory()->create();
    $tool = Tool::factory()->create();

    $product = Product::factory()->create([
        'serial_number' => 'DELETE-ME',
        'tool_id' => $tool->id,
    ]);

    $product->users()->attach($user->id);

    Visible::factory()->create(['product_id' => $product->id, 'isVisible' => true]);

    actingAs($user);

    Livewire::test(ProductSearchUser::class)
        ->callTableAction('delete', $product);

    // Product still exists
    expect(Product::find($product->id))->not->toBeNull();

    // But user is no longer associated with it
    expect($product->users()->where('user_id', $user->id)->exists())->toBeFalse();
});

it('shows non-visible products in the table', function () {
    $user = User::factory()->create();
    $tool = Tool::factory()->create();

    $product = Product::factory()->create([
        'serial_number' => 'HIDDEN-001',
        'tool_id' => $tool->id,
    ]);

    $product->users()->attach($user->id);

    Visible::factory()->create([
        'product_id' => $product->id,
        'isVisible' => false,
    ]);

    actingAs($user);

    Livewire::test(ProductSearchUser::class)
        ->assertCanSeeTableRecords([$product])
        ->assertSee('HIDDEN-001');
});

it('only shows products for authenticated user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $tool = Tool::factory()->create();

    $product1 = Product::factory()->create([
        'serial_number' => 'USER1-PRODUCT',
        'tool_id' => $tool->id,
    ]);
    $product2 = Product::factory()->create([
        'serial_number' => 'USER2-PRODUCT',
        'tool_id' => $tool->id,
    ]);

    $product1->users()->attach($user1->id);
    $product2->users()->attach($user2->id);

    Visible::factory()->create(['product_id' => $product1->id, 'isVisible' => true]);
    Visible::factory()->create(['product_id' => $product2->id, 'isVisible' => true]);

    actingAs($user1);

    Livewire::test(ProductSearchUser::class)
        ->assertCanSeeTableRecords([$product1])
        ->assertCanNotSeeTableRecords([$product2]);
});
