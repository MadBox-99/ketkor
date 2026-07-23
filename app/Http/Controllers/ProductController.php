<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Partial;
use App\Models\Product;
use App\Models\Tool;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $partials = Partial::query()->where('product_id', $product->id)->latest()->limit(6)->get();
        $users = User::query()->orderBy('name')->get();
        $tools = Tool::query()->orderBy('name')->get();

        return view('product.edit', ['users' => $users, 'tools' => $tools, 'product' => $product, 'partials' => $partials]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'tool_id' => ['required', 'exists:tools,id'],
                'user_ids' => ['required', 'array'],
                'user_ids.*' => ['exists:users,id'],
            ]);
            $product->update([
                'serial_number' => $request->serial_number,
                'installation_date' => $request->installation_date,
                'warrantee_date' => $request->warrantee_date,
                'purchase_date' => $request->purchase_date,
                'owner_name' => $request->owner_name,
                'city' => $request->city,
                'street' => $request->street,
                'zip' => $request->zip,
                'tool_id' => $request->tool_id,
            ]);
            $product->users()->sync($request->user_ids);

            DB::commit();

            return to_route('products.edit', ['product' => $product])->with('success', __('Products updated successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return to_route('products.edit', ['product' => $product])->with('error', $throwable->getMessage());
        }
    }

    public function add(Product $product): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->products()->attach($product->id);

        return to_route('products.edit', ['product' => $product]);
    }

    public function remove(Product $product): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->products()->detach($product->id);
        Notification::make()
            ->title(__('Succesfuly removed the product from your account.'))
            ->success()
            ->send();

        return to_route('products.myproducts');
    }

    public function search(): Factory|View
    {
        return view('product.search');
    }

    public function myproducts(): View
    {
        return view('product.myproduct');
    }
}
