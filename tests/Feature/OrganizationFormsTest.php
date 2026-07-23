<?php

declare(strict_types=1);

use App\Livewire\Organizations\Create;
use App\Livewire\Organizations\Edit;
use App\Models\Organization;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('renders the create page', function (): void {
    actingAs(User::factory()->createOne());

    get(route('organizations.create'))->assertOk()->assertSeeLivewire(Create::class);
});

it('creates an organization and assigns it to the current user', function (): void {
    $user = User::factory()->createOne(['organization_id' => null]);
    actingAs($user);

    livewire(Create::class)
        ->set('name', 'Acme')
        ->set('tax_number', '12345678-1-42')
        ->call('save')
        ->assertRedirect(route('organizations.myorganization'));

    assertDatabaseHas(Organization::class, ['name' => 'Acme']);

    expect($user->fresh()->organization_id)->not->toBeNull();
});

it('requires a name and a tax number', function (): void {
    actingAs(User::factory()->createOne());

    livewire(Create::class)
        ->set('name', '')
        ->set('tax_number', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required', 'tax_number' => 'required']);
});

it('updates an organization', function (): void {
    actingAs(User::factory()->createOne());
    $organization = Organization::factory()->createOne(['name' => 'Old']);

    livewire(Edit::class, ['organization' => $organization])
        ->set('name', 'New')
        ->call('save')
        ->assertRedirect(route('organizations.index'));

    expect($organization->fresh()->name)->toBe('New');
});
