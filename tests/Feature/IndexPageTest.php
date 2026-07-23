<?php

declare(strict_types=1);

use App\Livewire\Index;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('renders the index page as a full-page livewire component', function (): void {
    actingAs(User::factory()->createOne());

    get(route('index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});
