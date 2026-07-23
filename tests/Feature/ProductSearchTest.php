<?php

declare(strict_types=1);

use App\Livewire\Products\Search;
use App\Models\Product;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    actingAs(User::factory()->createOne());
});

it('renders the product search component', function (): void {
    Livewire::test(Search::class)
        ->assertStatus(200);
});

it('finds a product by serial number', function (): void {
    $tool = Tool::factory()->createOne();
    $product = Product::factory()->createOne([
        'serial_number' => 'FIND-ME-123',
        'tool_id' => $tool->id,
    ]);

    Livewire::test(Search::class)
        ->set('serial_number', 'FIND-ME-123')
        ->call('find')
        ->assertSet('product.id', $product->id);
});

it('returns null when product is not found', function (): void {
    Livewire::test(Search::class)
        ->set('serial_number', 'NONEXISTENT-999')
        ->call('find')
        ->assertSet('product', null);
});

it('validates serial number is required', function (): void {
    Livewire::test(Search::class)
        ->set('serial_number', '')
        ->call('find')
        ->assertHasErrors(['serial_number' => 'required']);
});

it('validates serial number minimum length', function (): void {
    Livewire::test(Search::class)
        ->set('serial_number', 'AB')
        ->call('find')
        ->assertHasErrors(['serial_number' => 'min']);
});

it('detects if current user owns the product', function (): void {
    $user = User::factory()->createOne();
    $tool = Tool::factory()->createOne();
    $product = Product::factory()->createOne([
        'serial_number' => 'OWNED-001',
        'tool_id' => $tool->id,
    ]);
    $product->users()->attach($user->id);

    actingAs($user);

    Livewire::test(Search::class)
        ->set('serial_number', 'OWNED-001')
        ->call('find')
        ->assertSet('owns', true);
});

it('detects if current user does not own the product', function (): void {
    $tool = Tool::factory()->createOne();
    Product::factory()->createOne([
        'serial_number' => 'NOT-OWNED-001',
        'tool_id' => $tool->id,
    ]);

    Livewire::test(Search::class)
        ->set('serial_number', 'NOT-OWNED-001')
        ->call('find')
        ->assertSet('owns', false);
});

it('renders as a full-page livewire component', function (): void {
    actingAs(User::factory()->createOne());

    get(route('products.search'))
        ->assertOk()
        ->assertSeeLivewire(Search::class);
});

it('attaches the found product to the current user', function (): void {
    $user = User::factory()->createOne();
    $product = Product::factory()->createOne();

    actingAs($user);

    Livewire::test(Search::class)
        ->set('serial_number', $product->serial_number)
        ->call('find')
        ->call('addToMyProducts')
        ->assertRedirect(route('products.edit', ['product' => $product]));

    expect($user->fresh()->products)->toHaveCount(1);
});

it('no longer exposes a GET route for attaching products', function (): void {
    expect(Route::has('products.add'))->toBeFalse();
});
