<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ProductSearch extends Component
{
    #[Rule('required|min:3|max:20')]
    public $serial_number = '';

    public ?Product $product = null;

    public bool $owns = false;

    public function find(): Factory|View
    {
        $user = Auth::user();
        $this->validate();
        $product = Product::with(['tool', 'product_logs'])
            ->where('serial_number', $this->serial_number)
            ->first();
        $this->owns = $user->products()->where('serial_number', $this->serial_number)->exists();
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
