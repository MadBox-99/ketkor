<?php

declare(strict_types=1);

use App\Livewire\ProductSearch;
use App\Models\Product;
use App\Models\Tool;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    actingAs(User::factory()->create());
});

it('renders the product search component', function (): void {
    Livewire::test(ProductSearch::class)
        ->assertStatus(200);
});

it('finds a product by serial number', function (): void {
    $tool = Tool::factory()->create();
    $product = Product::factory()->create([
        'serial_number' => 'FIND-ME-123',
        'tool_id' => $tool->id,
    ]);

    Livewire::test(ProductSearch::class)
        ->set('serial_number', 'FIND-ME-123')
        ->call('find')
        ->assertSet('product.id', $product->id);
});

it('returns null when product is not found', function (): void {
    Livewire::test(ProductSearch::class)
        ->set('serial_number', 'NONEXISTENT-999')
        ->call('find')
        ->assertSet('product', null);
});

it('validates serial number is required', function (): void {
    Livewire::test(ProductSearch::class)
        ->set('serial_number', '')
        ->call('find')
        ->assertHasErrors(['serial_number' => 'required']);
});

it('validates serial number minimum length', function (): void {
    Livewire::test(ProductSearch::class)
        ->set('serial_number', 'AB')
        ->call('find')
        ->assertHasErrors(['serial_number' => 'min']);
});

it('detects if current user owns the product', function (): void {
    $user = User::factory()->create();
    $tool = Tool::factory()->create();
    $product = Product::factory()->create([
        'serial_number' => 'OWNED-001',
        'tool_id' => $tool->id,
    ]);
    $product->users()->attach($user->id);

    actingAs($user);

    Livewire::test(ProductSearch::class)
        ->set('serial_number', 'OWNED-001')
        ->call('find')
        ->assertSet('owns', true);
});

it('detects if current user does not own the product', function (): void {
    $tool = Tool::factory()->create();
    Product::factory()->create([
        'serial_number' => 'NOT-OWNED-001',
        'tool_id' => $tool->id,
    ]);

    Livewire::test(ProductSearch::class)
        ->set('serial_number', 'NOT-OWNED-001')
        ->call('find')
        ->assertSet('owns', false);
});
