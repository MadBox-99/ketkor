<?php

namespace App\Livewire;

use Livewire\Component;

class ListProducts extends Component
{
    public $products;

    public function render()
    {
        return view('livewire.product.list-products');
    }
}
