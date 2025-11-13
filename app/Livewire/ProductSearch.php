<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ProductSearch extends Component
{
    #[Rule('required|min:3|max:255')]
    public $serial_number = '';

    public ?Product $product = null;

    public bool $owns = false;

    public function find(): Factory|View
    {
        $user = Auth::user();
        $this->validate();
        $product = Product::query()
            ->whereSerialNumber($this->serial_number)
            ->first();
        $this->owns = $user->whereHas('products', fn ($query) => $query->whereSerialNumber($this->serial_number))->exists();
        $this->product = $product;

        return view('livewire.product-search');
    }

    public function render(): Factory|View
    {
        $product = $this->product;

        // $owns = true;
        return view('livewire.product-search', ['product' => $product]);
    }
}
