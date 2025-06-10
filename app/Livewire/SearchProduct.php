<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SearchProduct extends Component
{
    use WithPagination;

    #[Url]
    public $owner_name = '';

    public function render()
    {
        if ($this->owner_name != '') {
            $products = Product::with([
                'partials' => function ($query): void {
                    $query->latest()->limit(1);
                },
            ])->where('owner_name', 'LIKE', '%'.$this->owner_name.'%')->paginate(10);
        } else {
            $products = Product::with([
                'partials' => function ($query): void {
                    $query->latest()->limit(1);
                },
            ])->paginate(10);
        }

        return view('livewire.search-product', ['products' => $products]);

    }
}
