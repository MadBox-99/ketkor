<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    foreach (UserRole::cases() as $role) {
        Role::query()->firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }
});

it('links the create employee button to the employee create route', function (): void {
    $organization = Organization::factory()->createOne([
        'name' => 'Test Org',
    ]);
    $organizer = User::factory()->createOne([
        'organization_id' => $organization->id,
    ]);
    $organizer->assignRole(UserRole::Organizer);

    actingAs($organizer);

    get(route('organizations.myorganization'))->assertOk()->assertSeeHtml(route('organizations.employee.create'))->assertDontSeeHtml('href="' . route('organizations.create') . '"');
});
