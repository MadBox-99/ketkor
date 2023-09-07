<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Partial;
use App\Models\Product;
use App\Models\Visible;
use Livewire\Component;
use Livewire\WithPagination;

class ProductSearchUser extends Component
{
    use WithPagination;
    #[URL]
    public $owner_name = '';
    public function render()
    {
        $user = User::find(1);
        if ($this->owner_name != '') {
            $products = Product::with([
                'partials' => function ($query) {
                    $query->latest()->first();
                },
                'visible' => function ($query) use ($user) {
                    $query->where('user_id', $user->id)->first();
                }
            ])->whereRelation('users', 'user_id', $user->id)
                ->where('owner_name', 'LIKE', '%' . $this->owner_name . '%')
                ->get();

        } else {
            $products = Product::with([
                'partials' => function ($query) {
                    $query->latest()->limit(1);
                },
                'visible' => function ($query) use ($user) {
                    $query->where('visibles.user_id', $user->id)->latest()->limit(1);
                }
            ])->whereRelation('users', 'user_id', $user->id)->paginate(10);
        }
        return view('livewire.product-search-user', ['products' => $products]);
    }
}