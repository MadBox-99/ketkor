<?php

declare(strict_types=1);

use App\Livewire\ProductEdit;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\Tool;
use App\Models\User;
use App\Models\Visible;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->tool = Tool::factory()->create();
    actingAs($this->user);

    $this->createProduct = function (array $attributes = []) {
        $product = Product::factory()->create(array_merge([
            'tool_id' => $this->tool->id,
        ], $attributes));

        Visible::factory()->create(['product_id' => $product->id, 'isVisible' => true]);

        return $product;
    };
});

describe('commissioning validation', function (): void {
    it('allows commissioning within 6 months of purchase', function (): void {
        $product = ($this->createProduct)(['purchase_date' => now()->subMonths(3)]);

        Livewire::test(ProductEdit::class, ['product' => $product, 'userVisibility' => true])
            ->set('eventData.what', 'commissioning')
            ->set('eventData.comment', 'First commissioning')
            ->call('createEvent');

        expect(ProductLog::query()->where('product_id', $product->id)->where('what', 'commissioning')->exists())->toBeTrue();

        // Check that installation_date and warrantee_date were set
        $product->refresh();
        expect($product->installation_date)->not->toBeNull();
        expect($product->warrantee_date)->not->toBeNull();
        expect($product->warrantee_date->greaterThan(now()))->toBeTrue();
    });

    it('prevents commissioning if purchase date is missing', function (): void {
        $product = ($this->createProduct)(['purchase_date' => null]);

        Livewire::test(ProductEdit::class, ['product' => $product, 'userVisibility' => true])
            ->set('eventData.what', 'commissioning')
            ->set('eventData.comment', 'Commissioning without purchase date')
            ->call('createEvent');

        expect(ProductLog::query()->where('product_id', $product->id)->where('what', 'commissioning')->exists())->toBeFalse();
    });
});

describe('maintenance validation', function (): void {
    it('allows maintenance 11-13 months after commissioning', function (): void {
        $product = ($this->createProduct)(['purchase_date' => now()->subMonths(13)]);

        ProductLog::query()->create([
            'product_id' => $product->id,
            'what' => 'commissioning',
            'comment' => 'Initial commissioning',
            'when' => now()->subMonths(11),
        ]);

        Livewire::test(ProductEdit::class, ['product' => $product, 'userVisibility' => true])
            ->set('eventData.what', 'maintenance')
            ->set('eventData.comment', 'First maintenance')
            ->call('createEvent');

        expect(ProductLog::query()->where('product_id', $product->id)->where('what', 'maintenance')->count())->toBe(1);

        // Check that warrantee_date was extended
        $product->refresh();
        expect($product->warrantee_date->greaterThan(now()))->toBeTrue();
    });

    it('allows maintenance without commissioning', function (): void {
        $product = ($this->createProduct)(['purchase_date' => now()->subMonths(12)]);

        Livewire::test(ProductEdit::class, ['product' => $product, 'userVisibility' => true])
            ->set('eventData.what', 'maintenance')
            ->set('eventData.comment', 'Maintenance without commissioning')
            ->call('createEvent');

        expect(ProductLog::query()->where('product_id', $product->id)->where('what', 'maintenance')->count())->toBe(1);
    });

    it('allows multiple maintenance operations', function (): void {
        $product = ($this->createProduct)(['purchase_date' => now()->subMonths(36)]);

        ProductLog::query()->create([
            'product_id' => $product->id,
            'what' => 'commissioning',
            'comment' => 'Initial commissioning',
            'when' => now()->subMonths(35),
        ]);

        ProductLog::query()->create([
            'product_id' => $product->id,
            'what' => 'maintenance',
            'comment' => 'First maintenance',
            'when' => now()->subMonths(24),
        ]);

        ProductLog::query()->create([
            'product_id' => $product->id,
            'what' => 'maintenance',
            'comment' => 'Second maintenance',
            'when' => now()->subMonths(12),
        ]);

        Livewire::test(ProductEdit::class, ['product' => $product, 'userVisibility' => true])
            ->set('eventData.what', 'maintenance')
            ->set('eventData.comment', 'Third maintenance')
            ->call('createEvent');

        expect(ProductLog::query()->where('product_id', $product->id)->where('what', 'maintenance')->count())->toBe(3);
    });

    it('validates second maintenance window from first maintenance', function (): void {
        $product = ($this->createProduct)(['purchase_date' => now()->subMonths(26)]);

        ProductLog::query()->create([
            'product_id' => $product->id,
            'what' => 'commissioning',
            'comment' => 'Initial commissioning',
            'when' => now()->subMonths(25),
        ]);

        ProductLog::query()->create([
            'product_id' => $product->id,
            'what' => 'maintenance',
            'comment' => 'First maintenance',
            'when' => now()->subMonths(12),
        ]);

        Livewire::test(ProductEdit::class, ['product' => $product, 'userVisibility' => true])
            ->set('eventData.what', 'maintenance')
            ->set('eventData.comment', 'Second maintenance')
            ->call('createEvent');

        expect(ProductLog::query()->where('product_id', $product->id)->where('what', 'maintenance')->count())->toBe(2);
    });

    it('does not extend warranty date on maintenance', function (): void {
        $product = ($this->createProduct)(['purchase_date' => now()->subMonths(24)]);

        ProductLog::query()->create([
            'product_id' => $product->id,
            'what' => 'commissioning',
            'comment' => 'Initial commissioning',
            'when' => now()->subMonths(23),
        ]);

        $product->update(['warrantee_date' => now()->addMonths(1)]);
        $originalWarrantyDate = $product->fresh()->warrantee_date->format('Y-m-d H:i:s');

        Livewire::test(ProductEdit::class, ['product' => $product, 'userVisibility' => true])
            ->set('eventData.what', 'maintenance')
            ->set('eventData.comment', 'First maintenance')
            ->call('createEvent');

        $product->refresh();
        expect($product->warrantee_date->format('Y-m-d H:i:s'))->toBe($originalWarrantyDate);
    });
});
