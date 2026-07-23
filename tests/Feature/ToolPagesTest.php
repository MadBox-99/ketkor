<?php

declare(strict_types=1);

use App\Enums\ProductCategory;
use App\Enums\UserRole;
use App\Livewire\Tools\Create;
use App\Livewire\Tools\Edit;
use App\Livewire\Tools\Index;
use App\Models\Tool;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    foreach (UserRole::cases() as $role) {
        Role::query()->firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }

    $admin = User::factory()->createOne();
    $admin->assignRole(UserRole::Admin);

    actingAs($admin);
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

it('persists a valid product category selected from the enum-backed dropdown options', function (): void {
    livewire(Create::class)
        ->set('name', 'Heat Pump 3000')
        ->set('category', ProductCategory::SPRSUN->value)
        ->call('save')
        ->assertRedirect(route('tools.index'));

    assertDatabaseHas(Tool::class, [
        'name' => 'Heat Pump 3000',
        'category' => ProductCategory::SPRSUN->value,
    ]);
});

it('forbids a user without a privileged role from viewing the tools index', function (): void {
    actingAs(User::factory()->createOne());

    get(route('tools.index'))->assertForbidden();
});

it('forbids a user without a privileged role from viewing the tools create page', function (): void {
    actingAs(User::factory()->createOne());

    get(route('tools.create'))->assertForbidden();
});

it('forbids a user without a privileged role from viewing the tools edit page', function (): void {
    $tool = Tool::factory()->createOne();

    actingAs(User::factory()->createOne());

    get(route('tools.edit', ['tool' => $tool]))->assertForbidden();
});

it('allows an admin to view the tools index', function (): void {
    get(route('tools.index'))->assertOk();
});

it('prevents a non-privileged user from deleting a tool via the table action', function (): void {
    $tool = Tool::factory()->createOne();

    actingAs(User::factory()->createOne());

    livewire(Index::class)->callAction(TestAction::make('delete')->table($tool));

    expect(Tool::query()->whereKey($tool->id)->exists())->toBeTrue();
});
