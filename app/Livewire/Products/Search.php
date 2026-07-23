<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Search extends Component
{
    #[Validate('required|min:3|max:255')]
    public string $serial_number = '';

    public ?Product $product = null;

    public bool $owns = false;

    public function find(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->validate();

        $product = Product::query()
            ->whereSerialNumber($this->serial_number)
            ->first();

        $this->product = $product;
        $this->owns = $product instanceof Product
            && $product->users->where('id', $user->id)->isNotEmpty();
    }

    public function addToMyProducts(): void
    {
        if (! $this->product instanceof Product) {
            return;
        }

        /** @var User $user */
        $user = Auth::user();
        $user->products()->attach($this->product->id);

        $this->redirectRoute('products.edit', ['product' => $this->product], navigate: true);
    }

    public function render(): Factory|View
    {
        return view('livewire.products.search', ['product' => $this->product]);
    }
}
