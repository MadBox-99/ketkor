<?php

declare(strict_types=1);

use App\Livewire\Organizations\Index;
use App\Livewire\Organizations\UsersTable;
use App\Models\Organization;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->createOne());
});

it('renders the organizations index', function (): void {
    get(route('organizations.index'))->assertOk()->assertSeeLivewire(Index::class);
});

it('lists organizations in the table', function (): void {
    $organizations = Organization::factory()->count(3)->create();

    livewire(Index::class)->assertCanSeeTableRecords($organizations);
});

it('lists only the given organization members in the users table', function (): void {
    $organization = Organization::factory()->createOne();
    $member = User::factory()->createOne(['organization_id' => $organization->id]);
    $outsider = User::factory()->createOne();

    livewire(UsersTable::class, ['organization' => $organization->id])
        ->assertCanSeeTableRecords([$member])
        ->assertCanNotSeeTableRecords([$outsider]);
});
