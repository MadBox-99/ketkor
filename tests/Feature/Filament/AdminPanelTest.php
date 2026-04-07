<?php

declare(strict_types=1);

use App\Filament\Resources\Logs\Pages\ListLogs;
use App\Filament\Resources\Organizations\Pages\ListOrganizations;
use App\Filament\Resources\Partials\Pages\ListPartials;
use App\Filament\Resources\ProductLogs\Pages\ListProductLogs;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Tools\Pages\ListTools;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Role;

function createAdminUser(): User
{
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Super-Admin', 'web');

    $user = User::factory()->create();
    $user->assignRole('Admin');

    return $user;
}

it('redirects unauthenticated users from admin panel', function (): void {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('renders the admin login page', function (): void {
    $this->get('/admin/login')->assertOk();
});

it('allows admin users to access admin panel', function (): void {
    $user = createAdminUser();

    actingAs($user);

    $this->get('/admin')->assertOk();
});

it('denies non-admin users access to admin panel', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    $this->get('/admin')->assertForbidden();
});

describe('resource list pages render correctly', function (): void {
    beforeEach(function (): void {
        actingAs(createAdminUser());
    });

    it('renders the products list page', function (): void {
        livewire(ListProducts::class)->assertSuccessful();
    });

    it('renders the users list page', function (): void {
        livewire(ListUsers::class)->assertSuccessful();
    });

    it('renders the organizations list page', function (): void {
        livewire(ListOrganizations::class)->assertSuccessful();
    });

    it('renders the tools list page', function (): void {
        livewire(ListTools::class)->assertSuccessful();
    });

    it('renders the logs list page', function (): void {
        livewire(ListLogs::class)->assertSuccessful();
    });

    it('renders the partials list page', function (): void {
        livewire(ListPartials::class)->assertSuccessful();
    });

    it('renders the product logs list page', function (): void {
        livewire(ListProductLogs::class)->assertSuccessful();
    });
});
