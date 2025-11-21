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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Product $product): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $product = Product::query()->find($product->id);

        $partials = Partial::query()->where('product_id', $product->id)->latest()->limit(6)->get();
        $users = User::query()->orderBy('name')->get();
        $tools = Tool::query()->orderBy('name')->get();

        return view('product.edit', ['users' => $users, 'tools' => $tools, 'product' => $product, 'partials' => $partials]);
    }

    public function partialUpdate(Request $request, Product $product) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        Auth::user();
        DB::beginTransaction();
        try {
            $request->validate([
                'tool_id' => ['required'],
                'user_ids' => ['required'],
            ]);
            // Validate Request
            $product = Product::whereId($product->id)->first();
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
            $users = User::query()->orderBy('name')->get();
            $tools = Tool::query()->orderBy('name')->get();
            $partials = Partial::query()->where('product_id', $product->id)->latest()->limit(6)->get();

            $success = __('Products updated successfully.');

            return to_route('products.edit', ['product' => $product])->with(['success' => $success, 'users' => $users, 'tools' => $tools, 'product' => $product, 'partials' => $partials]);
        } catch (Throwable $throwable) {
            DB::rollback();

            $partials = Partial::query()->where('product_id', $product->id)->latest()->limit(6)->get();
            $users = User::query()->orderBy('name')->get();
            $tools = Tool::query()->orderBy('name')->get();
            $error = $throwable->getMessage();

            return to_route('products.edit', ['product' => $product])->with(['error' => $error, 'users' => $users, 'tools' => $tools, 'product' => $product, 'partials' => $partials]);
        }
    }

    public function add(Product $product)
    {
        $user = Auth::user();
        $user->products()->attach($product->id);
        $product = Product::whereId($product->id)->first();

        $partials = Partial::query()->where('product_id', $product->id)->latest()->limit(6)->get();
        $users = User::query()->orderBy('name')->get();
        $tools = Tool::query()->orderBy('name')->get();

        return to_route('products.edit', ['product' => $product])->with(['users' => $users, 'tools' => $tools, 'product' => $product, 'partials' => $partials]);
    }

    public function remove(Product $product)
    {
        Auth::user()->products()->detach($product->id);
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
