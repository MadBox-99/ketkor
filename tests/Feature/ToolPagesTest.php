<?php

declare(strict_types=1);

use App\Livewire\Tools\Create;
use App\Livewire\Tools\Edit;
use App\Livewire\Tools\Index;
use App\Models\Tool;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->createOne());
});

it('renders the tools index', function (): void {
    get(route('tools.index'))->assertOk()->assertSeeLivewire(Index::class);
});

it('lists tools in the table', function (): void {
    $tools = Tool::factory()->count(3)->create();

    livewire(Index::class)->assertCanSeeTableRecords($tools);
});

it('renders the create page', function (): void {
    get(route('tools.create'))->assertOk()->assertSeeLivewire(Create::class);
});

it('creates a tool', function (): void {
    livewire(Create::class)
        ->set('name', 'Drill')
        ->set('category', 'sime')
        ->call('save')
        ->assertRedirect(route('tools.index'));

    assertDatabaseHas(Tool::class, ['name' => 'Drill', 'category' => 'sime']);
});

it('requires a name when creating a tool', function (): void {
    livewire(Create::class)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

it('renders the edit page', function (): void {
    $tool = Tool::factory()->createOne();

    get(route('tools.edit', ['tool' => $tool]))->assertOk()->assertSeeLivewire(Edit::class);
});

it('updates a tool', function (): void {
    $tool = Tool::factory()->createOne(['name' => 'Old']);

    livewire(Edit::class, ['tool' => $tool])
        ->set('name', 'New')
        ->call('save')
        ->assertRedirect(route('tools.index'));

    expect($tool->fresh()->name)->toBe('New');
});
