<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Auth;

class ProductSearch extends Component
{
    #[Rule('required|min:3|max:20')]
    public $serial_number = '';
    public ?Product $product = null;
    public function find()
    {
        //$userId = auth()->user()->id;
        $this->validate();
        $user = User::whereId(1)->first();
        $userBelongProductCount = $user->products()->where('serial_number', $this->serial_number)->count();
        $product = Product::where('serial_number', $this->serial_number)->first();
        $this->product = $product;
        return view('livewire.product-search', ['product' => $product, 'owns' => $userBelongProductCount]);
    }

    public function render()
    {
        //$this->validate();
        /*$user = User::whereId(1)->first();
        $userBelongProductCount = User::whereId($user->id)->first()->products()->where('serial_number', $this->serial_number)->count();
        if ($userBelongProductCount) {
            $product = Product::where('serial_number', $this->serial_number)->first();
        } else {

        }*/
        $product = $this->product;
        $owns = true;
        return view('livewire.product-search', compact('product', 'owns'));

    }
}
