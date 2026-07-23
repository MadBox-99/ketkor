<?php

declare(strict_types=1);

use App\Livewire\Products\Index;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('renders as a full-page livewire component', function (): void {
    actingAs(User::factory()->createOne());

    get(route('products.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

it('lists products in the table', function (): void {
    actingAs(User::factory()->createOne());
    $products = Product::factory()->count(3)->create();

    livewire(Index::class)
        ->assertCanSeeTableRecords($products);
});
