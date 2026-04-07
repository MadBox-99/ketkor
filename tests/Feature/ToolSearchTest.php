<?php

declare(strict_types=1);

use App\Livewire\ToolSearch;
use App\Models\Tool;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    actingAs(User::factory()->create());
});

it('renders the tool search component', function (): void {
    Livewire::test(ToolSearch::class)
        ->assertStatus(200);
});

it('displays tools in the view', function (): void {
    Tool::factory()->create(['name' => 'Drill Machine']);
    Tool::factory()->create(['name' => 'Circular Saw']);

    Livewire::test(ToolSearch::class)
        ->assertSee('Drill Machine')
        ->assertSee('Circular Saw');
});

it('filters tools by name via wire:model.live', function (): void {
    Tool::factory()->create(['name' => 'Drill Machine']);
    Tool::factory()->create(['name' => 'Circular Saw']);
    Tool::factory()->create(['name' => 'Drill Press']);

    Livewire::test(ToolSearch::class)
        ->set('tool_name', 'Drill')
        ->assertSee('Drill Machine')
        ->assertSee('Drill Press')
        ->assertDontSee('Circular Saw');
});

it('shows no tools when filter has no matches', function (): void {
    Tool::factory()->create(['name' => 'Drill Machine']);

    Livewire::test(ToolSearch::class)
        ->set('tool_name', 'Nonexistent')
        ->assertDontSee('Drill Machine');
});
