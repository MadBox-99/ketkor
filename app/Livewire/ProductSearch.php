<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Auth;

class ProductSearch extends Component
{
    #[Rule('required|min:3|max:20')]
    public $serial_number = '';

    public ?Product $product = null;

    public bool $owns = false;

    public function find()
    {
        $user = Auth::user();
        $this->validate();
        $product = Product::where('serial_number', $this->serial_number)->first();
        $this->owns = $user->products()->where('serial_number', $this->serial_number)->exists();
        $this->product = $product;
        return view('livewire.product-search');
    }

    public function render()
    {
        $product = $this->product;
        //$owns = true;
        return view('livewire.product-search', ['product' => $product]);

    }
}