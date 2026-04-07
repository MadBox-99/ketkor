<?php

declare(strict_types=1);

use App\Livewire\ProductEdit;
use App\Models\Partial;
use App\Models\Product;
use App\Models\Tool;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

use Spatie\Permission\Models\Role;

function createUserWithRole(string ...$roles): User
{
    foreach ($roles as $role) {
        Role::findOrCreate($role, 'web');
    }

    $user = User::factory()->create();
    $user->assignRole($roles);

    return $user;
}

function createProductWithTool(array $attributes = []): Product
{
    $tool = Tool::factory()->create();

    return Product::factory()->create(array_merge([
        'tool_id' => $tool->id,
        'purchase_date' => now()->subMonths(3),
    ], $attributes));
}

describe('component rendering', function (): void {
    it('renders the product edit component', function (): void {
        actingAs(User::factory()->create());
        $product = createProductWithTool();

        Livewire::test(ProductEdit::class, ['product' => $product])
            ->assertStatus(200);
    });

    it('populates product form with existing data', function (): void {
        actingAs(User::factory()->create());
        $product = createProductWithTool([
            'serial_number' => 'PREFILLED-001',
            'city' => 'Budapest',
        ]);

        Livewire::test(ProductEdit::class, ['product' => $product])
            ->assertSet('productData.serial_number', 'PREFILLED-001')
            ->assertSet('productData.city', 'Budapest');
    });
});

describe('product update', function (): void {
    it('updates product data', function (): void {
        actingAs(User::factory()->create());
        $product = createProductWithTool();
        $newTool = Tool::factory()->create();

        Livewire::test(ProductEdit::class, ['product' => $product])
            ->set('productData.serial_number', 'UPDATED-001')
            ->set('productData.city', 'Debrecen')
            ->set('productData.street', 'Main Street')
            ->set('productData.zip', '4000')
            ->set('productData.purchase_date', now()->subMonths(2)->format('Y-m-d'))
            ->set('productData.installation_date', now()->subMonth()->format('Y-m-d'))
            ->set('productData.warrantee_date', now()->addYear()->format('Y-m-d'))
            ->set('productData.tool_id', $newTool->id)
            ->call('updateProduct')
            ->assertNotified();

        $product->refresh();
        expect($product->serial_number)->toBe('UPDATED-001');
        expect($product->city)->toBe('Debrecen');
    });

    it('syncs users when admin updates product', function (): void {
        $admin = createUserWithRole('Admin');
        actingAs($admin);

        $product = createProductWithTool();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Livewire::test(ProductEdit::class, ['product' => $product])
            ->set('productData.user_ids', [$user1->id, $user2->id])
            ->call('updateProduct');

        expect($product->users()->pluck('users.id')->toArray())
            ->toContain($user1->id)
            ->toContain($user2->id);
    });
});

describe('owner update', function (): void {
    it('creates a new partial with owner data', function (): void {
        actingAs(User::factory()->create());
        $product = createProductWithTool();

        Livewire::test(ProductEdit::class, ['product' => $product])
            ->set('ownerData.name', 'John Doe')
            ->set('ownerData.email', 'john@example.com')
            ->set('ownerData.phone', '+36301234567')
            ->call('updateOwner')
            ->assertNotified();

        expect(Partial::query()->where('product_id', $product->id)->count())->toBe(1);
        expect(Partial::query()->where('product_id', $product->id)->first())
            ->name->toBe('John Doe')
            ->email->toBe('john@example.com')
            ->phone->toBe('+36301234567');
    });

    it('populates owner form with latest partial data', function (): void {
        actingAs(User::factory()->create());
        $product = createProductWithTool();
        $product->partials()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+36301111111',
        ]);

        Livewire::test(ProductEdit::class, ['product' => $product])
            ->assertSet('ownerData.name', 'Jane Doe')
            ->assertSet('ownerData.email', 'jane@example.com')
            ->assertSet('ownerData.phone', '+36301111111');
    });
});

describe('event creation', function (): void {
    it('creates an installation event', function (): void {
        actingAs(User::factory()->create());
        $product = createProductWithTool();

        Livewire::test(ProductEdit::class, ['product' => $product])
            ->set('eventData.what', 'installation')
            ->set('eventData.comment', 'Installation done')
            ->call('createEvent')
            ->assertNotified();

        expect($product->product_logs()->where('what', 'installation')->count())->toBe(1);
    });

    it('creates an event with online flag', function (): void {
        actingAs(User::factory()->create());
        $product = createProductWithTool();

        Livewire::test(ProductEdit::class, ['product' => $product])
            ->set('eventData.what', 'installation')
            ->set('eventData.is_online', true)
            ->call('createEvent');

        expect($product->product_logs()->first()->is_online)->toBeTrue();
    });
});

describe('filament actions', function (): void {
    it('has generate worksheet action', function (): void {
        actingAs(User::factory()->create());
        $product = createProductWithTool();

        Livewire::test(ProductEdit::class, ['product' => $product])
            ->assertActionExists('generateWorksheet');
    });

    it('has view signature action', function (): void {
        actingAs(User::factory()->create());
        $product = createProductWithTool();

        Livewire::test(ProductEdit::class, ['product' => $product])
            ->assertActionExists('viewSignature');
    });
});
